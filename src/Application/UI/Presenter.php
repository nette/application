<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\UI;

use Nette;
use Nette\Application;
use Nette\Application\Helpers;
use Nette\Application\Responses;
use Nette\Http;
use Nette\Utils\Arrays;


/**
 * Presenter component represents a webpage instance. It converts Request to Response.
 *
 * @property-read Nette\Application\Request $request
 * @property-read string $action
 * @property      string $view
 * @property      string|bool $layout
 * @property-read \stdClass $payload
 * @property-read Nette\Http\Session $session
 * @property-read Nette\Security\User $user
 */
abstract class Presenter extends Control implements Application\IPresenter
{
	/** bad link handling {@link Presenter::$invalidLinkMode} */
	public const
		InvalidLinkSilent = 0b0000,
		InvalidLinkWarning = 0b0001,
		InvalidLinkException = 0b0010,
		InvalidLinkTextual = 0b0100;

	/** @internal special parameter key */
	public const
		PresenterKey = 'presenter',
		SignalKey = 'do',
		ActionKey = 'action',
		FlashKey = '_fid',
		DefaultAction = 'default';

	/** @deprecated use Presenter::InvalidLinkSilent */
	public const INVALID_LINK_SILENT = self::InvalidLinkSilent;

	/** @deprecated use Presenter::InvalidLinkWarning */
	public const INVALID_LINK_WARNING = self::InvalidLinkWarning;

	/** @deprecated use Presenter::InvalidLinkException */
	public const INVALID_LINK_EXCEPTION = self::InvalidLinkException;

	/** @deprecated use Presenter::InvalidLinkTextual */
	public const INVALID_LINK_TEXTUAL = self::InvalidLinkTextual;

	/** @deprecated use Presenter::PresenterKey */
	public const PRESENTER_KEY = self::PresenterKey;

	/** @deprecated use Presenter::SignalKey */
	public const SIGNAL_KEY = self::SignalKey;

	/** @deprecated use Presenter::ActionKey */
	public const ACTION_KEY = self::ActionKey;

	/** @deprecated use Presenter::FlashKey */
	public const FLASH_KEY = self::FlashKey;

	/** @deprecated use Presenter::DefaultAction */
	public const DEFAULT_ACTION = self::DefaultAction;

	public int $invalidLinkMode = 0;

	/** @var array<callable(self): void>  Occurs when the presenter is starting */
	public array $onStartup = [];

	/** @var array<callable(self): void>  Occurs when the presenter is rendering after beforeRender */
	public array $onRender = [];

	/** @var array<callable(self, Application\Response): void>  Occurs when the presenter is shutting down */
	public array $onShutdown = [];

	/** automatically call canonicalize() */
	public bool $autoCanonicalize = true;

	/** use absolute Urls or paths? */
	public bool $absoluteUrls = false;

	/** @deprecated  use #[Requires(methods: ...)] to specify allowed methods */
	public array $allowedMethods = ['GET', 'POST', 'HEAD', 'PUT', 'DELETE', 'PATCH'];
	private ?Nette\Application\Request $request = null;
	private ?Nette\Application\Response $response = null;
	private array $globalParams = [];
	private array $globalState;
	private ?array $globalStateSinces;
	private string $action = '';
	private string $view = '';
	private bool $forwarded = false;
	private string|bool $layout = '';
	private \stdClass $payload;
	private string $signalReceiver;
	private ?string $signal = null;
	private bool $ajaxMode;
	private bool $startupCheck = false;
	private ?Nette\Application\Request $lastCreatedRequest;
	private ?array $lastCreatedRequestFlag;
	private readonly Nette\Http\IRequest $httpRequest;
	private readonly Nette\Http\IResponse $httpResponse;
	private readonly ?Nette\Http\Session $session;
	private readonly ?Nette\Application\IPresenterFactory $presenterFactory;
	private readonly ?Nette\Routing\Router $router;
	private readonly ?Nette\Security\User $user;
	private readonly ?TemplateFactory $templateFactory;
	private Nette\Http\UrlScript $refUrlCache;


	public function __construct()
	{
	}


	final public function getRequest(): ?Application\Request
	{
		return $this->request;
	}


	/**
	 * Returns self.
	 */
	final public function getPresenter(): static
	{
		return $this;
	}


	final public function getPresenterIfExists(): static
	{
		return $this;
	}


	/** @deprecated */
	final public function hasPresenter(): bool
	{
		return true;
	}


	/**
	 * Returns a name that uniquely identifies component.
	 */
	public function getUniqueId(): string
	{
		return '';
	}


	public function isModuleCurrent(string $module): bool
	{
		$current = Helpers::splitName($this->getName())[0];
		return str_starts_with($current . ':', ltrim($module . ':', ':'));
	}


	public function isForwarded(): bool
	{
		return $this->forwarded || $this->request->isMethod($this->request::FORWARD);
	}


	/********************* interface IPresenter ****************d*g**/


	public function run(Application\Request $request): Application\Response
	{
		$this->request = $request;
		$this->setParent($this->getParent(), $request->getPresenterName());

		if (!$this->httpResponse->isSent()) {
			$this->httpResponse->addHeader('Vary', 'X-Requested-With');
		}

		$this->initGlobalParameters();

		try {
			// CHECK REQUIREMENTS
			(new AccessPolicy($this, static::getReflection()))->checkAccess();
			$this->checkRequirements(static::getReflection());
			$this->checkHttpMethod();

			// STARTUP
			Arrays::invoke($this->onStartup, $this);
			$this->startup();
			if (!$this->startupCheck) {
				$class = static::getReflection()->getMethod('startup')->getDeclaringClass()->getName();
				throw new Nette\InvalidStateException("Method $class::startup() or its descendant doesn't call parent::startup().");
			}

			// calls $this->action<Action>()
			try {
				actionMethod:
				$this->tryCall(static::formatActionMethod($this->action), $this->params);
			} catch (Application\SwitchException $e) {
				$this->changeAction($e->getMessage());
				$this->autoCanonicalize = false;
				goto actionMethod;
			}

			// autoload components
			foreach ($this->globalParams as $id => $foo) {
				$this->getComponent((string) $id, throw: false);
			}

			if ($this->autoCanonicalize) {
				$this->canonicalize();
			}

			if ($this->httpRequest->isMethod('head')) {
				$this->terminate();
			}

			// SIGNAL HANDLING
			// calls $this->handle<Signal>()
			$this->processSignal();

			// RENDERING VIEW
			$this->beforeRender();
			Arrays::invoke($this->onRender, $this);
			// calls $this->render<View>()
			try {
				renderMethod:
				$this->tryCall(static::formatRenderMethod($this->view), $this->params);
			} catch (Application\SwitchException $e) {
				$this->setView($e->getMessage());
				goto renderMethod;
			}
			$this->afterRender();

			// finish template rendering
			$this->sendTemplate();

		} catch (Application\SwitchException $e) {
			throw new \LogicException('Switch is only allowed inside action*() or render*() method.', 0, $e);
		} catch (Application\AbortException) {
		}

		// save component tree persistent state
		$this->saveGlobalState();

		if ($this->isAjax()) {
			$this->getPayload()->state = $this->getGlobalState();
			try {
				if ($this->response instanceof Responses\TextResponse && $this->isControlInvalid()) {
					$this->snippetMode = true;
					$this->response->send($this->httpRequest, $this->httpResponse);
					$this->sendPayload();
				}
			} catch (Application\AbortException) {
			}
		}

		if ($this->hasFlashSession()) {
			$this->getFlashSession()->setExpiration('30 seconds');
		}

		if (!$this->response) {
			$this->response = new Responses\VoidResponse;
		}

		Arrays::invoke($this->onShutdown, $this, $this->response);
		$this->shutdown($this->response);

		return $this->response;
	}


	/**
	 * @return void
	 */
	protected function startup()
	{
		$this->startupCheck = true;
	}


	/**
	 * Common render method.
	 * @return void
	 */
	protected function beforeRender()
	{
	}


	/**
	 * Common render method.
	 */
	protected function afterRender(): void
	{
	}


	protected function shutdown(Application\Response $response): void
	{
	}


	/**
	 * This method will be called when CSRF is detected.
	 */
	public function detectedCsrf(): void
	{
		try {
			$this->redirect('this');
		} catch (InvalidLinkException $e) {
			throw new Nette\Application\BadRequestException($e->getMessage());
		}
	}


	/** @deprecated  use #[Requires(methods: ...)] to specify allowed methods */
	protected function checkHttpMethod(): void
	{
		if ($this->allowedMethods &&
			!in_array($method = $this->httpRequest->getMethod(), $this->allowedMethods, strict: true)
		) {
			$this->httpResponse->setHeader('Allow', implode(',', $this->allowedMethods));
			$this->error("Method $method is not allowed", Nette\Http\IResponse::S405_MethodNotAllowed);
		}
	}


	/********************* signal handling ****************d*g**/


	/**
	 * @throws BadSignalException
	 */
	public function processSignal(): void
	{
		if (!isset($this->signal)) {
			return;
		}

		$component = $this->signalReceiver === ''
			? $this
			: $this->getComponent($this->signalReceiver, throw: false);
		if ($component === null) {
			throw new BadSignalException("The signal receiver component '$this->signalReceiver' is not found.");

		} elseif (!$component instanceof SignalReceiver) {
			throw new BadSignalException("The signal receiver component '$this->signalReceiver' is not SignalReceiver implementor.");
		}

		$component->signalReceived($this->signal);
		$this->signal = null;
	}


	/**
	 * Returns pair signal receiver and name.
	 */
	final public function getSignal(): ?array
	{
		return $this->signal === null ? null : [$this->signalReceiver, $this->signal];
	}


	/**
	 * Checks if the signal receiver is the given one.
	 */
	final public function isSignalReceiver(
		Nette\ComponentModel\Component|string $component,
		string|bool|null $signal = null,
	): bool
	{
		if ($component instanceof Nette\ComponentModel\Component) {
			$component = $component === $this
				? ''
				: $component->lookupPath(self::class);
		}

		if ($this->signal === null) {
			return false;

		} elseif ($signal === true) {
			return $component === ''
				|| strncmp($this->signalReceiver . '-', $component . '-', strlen($component) + 1) === 0;

		} elseif ($signal === null) {
			return $this->signalReceiver === $component;
		}

		return $this->signalReceiver === $component && strcasecmp($signal, $this->signal) === 0;
	}


	/********************* rendering ****************d*g**/


	/**
	 * Returns current action name.
	 */
	final public function getAction(bool $fullyQualified = false): string
	{
		return $fullyQualified
			? ':' . $this->getName() . ':' . $this->action
			: $this->action;
	}


	/**
	 * Changes current action.
	 */
	public function changeAction(string $action): void
	{
		$this->forwarded = true;
		$this->action = $this->view = $action;
	}


	/**
	 * Switch from current action or render method to another.
	 */
	public function switch(string $action): never
	{
		throw new Application\SwitchException($action);
	}


	/**
	 * Returns current view.
	 */
	final public function getView(): string
	{
		return $this->view;
	}


	/**
	 * Changes current view. Any name is allowed.
	 */
	public function setView(string $view): static
	{
		$this->forwarded = true;
		$this->view = $view;
		return $this;
	}


	/**
	 * Returns current layout name.
	 */
	final public function getLayout(): string|bool
	{
		return $this->layout;
	}


	/**
	 * Changes or disables layout.
	 */
	public function setLayout(string|bool $layout): static
	{
		$this->layout = $layout === false ? false : (string) $layout;
		return $this;
	}


	/**
	 * @throws Nette\Application\AbortException
	 * @return never
	 */
	public function sendTemplate(?Template $template = null): void
	{
		$template ??= $this->getTemplate();
		if (!$template->getFile()) {
			$template->setFile($this->findTemplateFile());
		}
		$this->sendResponse(new Responses\TextResponse($template));
	}


	/**
	 * Finds template file name.
	 */
	public function findTemplateFile(): string
	{
		$files = $this->formatTemplateFiles();
		foreach ($files as $file) {
			if (is_file($file)) {
				return $file;
			}
		}

		$file = strtr(Arrays::first($files), '/', DIRECTORY_SEPARATOR);
		$this->error("Page not found. Missing template '$file'.");
	}


	/**
	 * Finds layout template file name.
	 * @internal
	 */
	public function findLayoutTemplateFile(): ?string
	{
		if ($this->layout === false) {
			return null;
		}

		$files = $this->formatLayoutTemplateFiles();
		foreach ($files as $file) {
			if (is_file($file)) {
				return $file;
			}
		}

		if ($this->layout) {
			$file = strtr(Arrays::first($files), '/', DIRECTORY_SEPARATOR);
			throw new Nette\FileNotFoundException("Layout not found. Missing template '$file'.");
		}

		return null;
	}


	/**
	 * Formats layout template file names.
	 * @return string[]
	 */
	public function formatLayoutTemplateFiles(): array
	{
		if (preg_match('#/|\\\\#', (string) $this->layout)) {
			return [$this->layout];
		}

		[$module, $presenter] = Helpers::splitName($this->getName());
		$layout = $this->layout ?: 'layout';
		$dir = dirname(static::getReflection()->getFileName());
		$dir = is_dir("$dir/templates") ? $dir : dirname($dir);
		$list = [
			"$dir/templates/$presenter/@$layout.latte",
			"$dir/templates/$presenter.@$layout.latte",
		];
		do {
			$list[] = "$dir/templates/@$layout.latte";
			$dir = dirname($dir);
		} while ($dir && $module && ([$module] = Helpers::splitName($module)));

		return $list;
	}


	/**
	 * Formats view template file names.
	 * @return string[]
	 */
	public function formatTemplateFiles(): array
	{
		[, $presenter] = Helpers::splitName($this->getName());
		$dir = dirname(static::getReflection()->getFileName());
		$dir = is_dir("$dir/templates") ? $dir : dirname($dir);
		return [
			"$dir/templates/$presenter/$this->view.latte",
			"$dir/templates/$presenter.$this->view.latte",
		];
	}


	/**
	 * Formats action method name.
	 */
	public static function formatActionMethod(string $action): string
	{
		return 'action' . ucfirst($action);
	}


	/**
	 * Formats render view method name.
	 */
	public static function formatRenderMethod(string $view): string
	{
		return 'render' . ucfirst($view);
	}


	protected function createTemplate(?string $class = null): Template
	{
		$class ??= $this->formatTemplateClass();
		return $this->getTemplateFactory()->createTemplate($this, $class);
	}


	public function formatTemplateClass(): ?string
	{
		$base = preg_replace('#Presenter$#', '', static::class);
		return $this->checkTemplateClass($base . ucfirst($this->action) . 'Template')
			?? $this->checkTemplateClass($base . 'Template');
	}


	/********************* partial AJAX rendering ****************d*g**/


	final public function getPayload(): \stdClass
	{
		return $this->payload ??= new \stdClass;
	}


	/**
	 * Is AJAX request?
	 */
	public function isAjax(): bool
	{
		if (!isset($this->ajaxMode)) {
			$this->ajaxMode = $this->httpRequest->isAjax();
		}

		return $this->ajaxMode;
	}


	/**
	 * Sends AJAX payload to the output.
	 * @throws Nette\Application\AbortException
	 * @return never
	 */
	public function sendPayload(): void
	{
		$this->sendResponse(new Responses\JsonResponse($this->getPayload()));
	}


	/**
	 * Sends JSON data to the output.
	 * @throws Nette\Application\AbortException
	 * @return never
	 */
	public function sendJson(mixed $data): void
	{
		$this->sendResponse(new Responses\JsonResponse($data));
	}


	/********************* navigation & flow ****************d*g**/


	/**
	 * Sends response and terminates presenter.
	 * @throws Nette\Application\AbortException
	 * @return never
	 */
	public function sendResponse(Application\Response $response): void
	{
		$this->response = $response;
		$this->terminate();
	}


	/**
	 * Correctly terminates presenter.
	 * @throws Nette\Application\AbortException
	 * @return never
	 */
	public function terminate(): void
	{
		throw new Application\AbortException;
	}


	/**
	 * Forward to another presenter or action.
	 * @param  array|mixed  $args
	 * @throws Nette\Application\AbortException
	 * @return never
	 */
	public function forward(string|Nette\Application\Request $destination, $args = []): void
	{
		if ($destination instanceof Application\Request) {
			$this->sendResponse(new Responses\ForwardResponse($destination));
		}

		$args = func_num_args() < 3 && is_array($args)
			? $args
			: array_slice(func_get_args(), 1);
		$this->createRequest($this, $destination, $args, 'forward');
		$this->sendResponse(new Responses\ForwardResponse($this->lastCreatedRequest));
	}


	/**
	 * Redirect to another URL and ends presenter execution.
	 * @throws Nette\Application\AbortException
	 * @return never
	 */
	public function redirectUrl(string $url, ?int $httpCode = null): void
	{
		if ($this->isAjax()) {
			$this->getPayload()->redirect = $url;
			$this->sendPayload();

		} elseif (!$httpCode) {
			$httpCode = $this->httpRequest->isMethod('post')
				? Http\IResponse::S303_PostGet
				: Http\IResponse::S302_Found;
		}

		$this->sendResponse(new Responses\RedirectResponse($url, $httpCode));
	}


	/**
	 * Returns the last created Request.
	 * @internal
	 */
	final public function getLastCreatedRequest(): ?Application\Request
	{
		return $this->lastCreatedRequest;
	}


	/**
	 * Returns the last created Request flag.
	 * @internal
	 */
	final public function getLastCreatedRequestFlag(string $flag): bool
	{
		return !empty($this->lastCreatedRequestFlag[$flag]);
	}


	/**
	 * Conditional redirect to canonicalized URI.
	 * @param  mixed  ...$args
	 * @throws Nette\Application\AbortException
	 */
	public function canonicalize(?string $destination = null, ...$args): void
	{
		$request = $this->request;
		if ($this->isAjax() || (!$request->isMethod('get') && !$request->isMethod('head'))) {
			return;
		}

		$args = count($args) === 1 && is_array($args[0] ?? null)
			? $args[0]
			: $args;
		try {
			$url = $this->createRequest(
				$this,
				$destination ?: $this->action,
				$args + $this->getGlobalState() + $request->getParameters(),
				'redirectX',
			);
		} catch (InvalidLinkException) {
		}

		if (!isset($url) || $this->httpRequest->getUrl()->isEqual($url)) {
			return;
		}

		$code = $request->hasFlag($request::VARYING)
			? Http\IResponse::S302_Found
			: Http\IResponse::S301_MovedPermanently;
		$this->sendResponse(new Responses\RedirectResponse($url, $code));
	}


	/**
	 * Attempts to cache the sent entity by its last modification date.
	 * @param  string  $etag  strong entity tag validator
	 * @param  string  $expire  like '20 minutes'
	 * @throws Nette\Application\AbortException
	 */
	public function lastModified(
		string|int|\DateTimeInterface|null $lastModified,
		?string $etag = null,
		?string $expire = null,
	): void
	{
		if ($expire !== null) {
			$this->httpResponse->setExpiration($expire);
		}

		$helper = new Http\Context($this->httpRequest, $this->httpResponse);
		if (!$helper->isModified($lastModified, $etag)) {
			$this->terminate();
		}
	}


	/**
	 * Request/URL factory.
	 * @param  string   $destination in format "[//] [[[module:]presenter:]action | signal! | this] [#fragment]"
	 * @param  string   $mode  forward|redirect|link
	 * @throws InvalidLinkException
	 * @internal
	 */
	protected function createRequest(
		Component $component,
		string $destination,
		array $args,
		string $mode,
	): ?string
	{
		// note: createRequest supposes that saveState(), run() & tryCall() behaviour is final

		$this->lastCreatedRequest = $this->lastCreatedRequestFlag = null;

		$parts = static::parseDestination($destination);
		$path = $parts['path'];
		$args = $parts['args'] ?? $args;

		if (!$component instanceof self || $parts['signal']) {
			[$cname, $signal] = Helpers::splitName($path);
			if ($cname !== '') {
				$component = $component->getComponent(strtr($cname, ':', '-'));
			}

			if ($signal === '') {
				throw new InvalidLinkException('Signal must be non-empty string.');
			}

			$path = 'this';
		}

		$current = false;
		[$presenter, $action] = Helpers::splitName($path);
		if ($presenter === '') {
			$action = $path === 'this' ? $this->action : $action;
			$presenter = $this->getName();
			$presenterClass = static::class;

		} else {
			if ($presenter[0] === ':') { // absolute
				$presenter = substr($presenter, 1);
				if (!$presenter) {
					throw new InvalidLinkException("Missing presenter name in '$destination'.");
				}
			} else { // relative
				[$module, , $sep] = Helpers::splitName($this->getName());
				$presenter = $module . $sep . $presenter;
			}

			if (empty($this->presenterFactory)) {
				throw new Nette\InvalidStateException('Unable to create link to other presenter, service PresenterFactory has not been set.');
			}

			try {
				$presenterClass = $this->presenterFactory->getPresenterClass($presenter);
			} catch (Application\InvalidPresenterException $e) {
				throw new InvalidLinkException($e->getMessage(), 0, $e);
			}
		}

		// PROCESS SIGNAL ARGUMENTS
		if (isset($signal)) { // $component must be StatePersistent
			$reflection = new ComponentReflection($component::class);
			if ($signal === 'this') { // means "no signal"
				$signal = '';
				if (array_key_exists(0, $args)) {
					throw new InvalidLinkException("Unable to pass parameters to 'this!' signal.");
				}
			} elseif (!str_contains($signal, self::NameSeparator)) {
				// counterpart of signalReceived() & tryCall()
				$method = $reflection->getSignalMethod($signal);
				if (!$method) {
					throw new InvalidLinkException("Unknown signal '$signal', missing handler {$reflection->getName()}::{$component::formatSignalMethod($signal)}()");
				} elseif ($this->invalidLinkMode && ComponentReflection::parseAnnotation($method, 'deprecated')) {
					trigger_error("Link to deprecated signal '$signal'" . ($component === $this ? '' : ' in ' . $component::class) . " from '{$this->getName()}:{$this->getAction()}'.", E_USER_DEPRECATED);
				}

				// convert indexed parameters to named
				ParameterConverter::toParameters($component::class, $method->getName(), $args, [], $missing);
			}

			// counterpart of StatePersistent
			if ($args && array_intersect_key($args, $reflection->getPersistentParams())) {
				$component->saveState($args);
			}

			if ($args && $component !== $this) {
				$prefix = $component->getUniqueId() . self::NameSeparator;
				foreach ($args as $key => $val) {
					unset($args[$key]);
					$args[$prefix . $key] = $val;
				}
			}
		}

		// PROCESS ARGUMENTS
		if (is_subclass_of($presenterClass, self::class)) {
			if ($action === '') {
				$action = self::DefaultAction;
			}

			$current = ($action === '*' || strcasecmp($action, $this->action) === 0) && $presenterClass === static::class;

			$reflection = new ComponentReflection($presenterClass);
			if ($this->invalidLinkMode && ComponentReflection::parseAnnotation($reflection, 'deprecated')) {
				trigger_error("Link to deprecated presenter '$presenter' from '{$this->getName()}:{$this->getAction()}'.", E_USER_DEPRECATED);
			}

			// counterpart of run() & tryCall()
			if ($method = $reflection->getActionRenderMethod($action)) {
				if ($this->invalidLinkMode && ComponentReflection::parseAnnotation($method, 'deprecated')) {
					trigger_error("Link to deprecated action '$presenter:$action' from '{$this->getName()}:{$this->getAction()}'.", E_USER_DEPRECATED);
				}

				ParameterConverter::toParameters($presenterClass, $method->getName(), $args, $path === 'this' ? $this->params : [], $missing);

			} elseif (array_key_exists(0, $args)) {
				throw new InvalidLinkException("Unable to pass parameters to action '$presenter:$action', missing corresponding method.");
			}

			// counterpart of StatePersistent
			if ($args && array_intersect_key($args, $reflection->getPersistentParams())) {
				$this->saveStatePartial($args, $reflection);
			}

			if ($mode === 'redirect') {
				$this->saveGlobalState();
			}

			$globalState = $this->getGlobalState($path === 'this' ? null : $presenterClass);
			if ($current && $args) {
				$tmp = $globalState + $this->params;
				foreach ($args as $key => $val) {
					if (http_build_query([$val]) !== (isset($tmp[$key]) ? http_build_query([$tmp[$key]]) : '')) {
						$current = false;
						break;
					}
				}
			}

			$args += $globalState;
		}

		if ($mode !== 'test' && !empty($missing)) {
			foreach ($missing as $rp) {
				if (!array_key_exists($rp->getName(), $args)) {
					throw new InvalidLinkException("Missing parameter \${$rp->getName()} required by {$rp->getDeclaringClass()->getName()}::{$rp->getDeclaringFunction()->getName()}()");
				}
			}
		}

		// ADD ACTION & SIGNAL & FLASH
		if ($action) {
			$args[self::ActionKey] = $action;
		}

		if (!empty($signal)) {
			$args[self::SignalKey] = $component->getParameterId($signal);
			$current = $current && $args[self::SignalKey] === $this->getParameter(self::SignalKey);
		}

		if (($mode === 'redirect' || $mode === 'forward') && $this->hasFlashSession()) {
			$args[self::FlashKey] = $this->getFlashKey();
		}

		$this->lastCreatedRequest = new Application\Request($presenter, Application\Request::FORWARD, $args);
		$this->lastCreatedRequestFlag = ['current' => $current];

		return $mode === 'forward' || $mode === 'test'
			? null
			: $this->requestToUrl($this->lastCreatedRequest, $mode === 'link' && !$parts['absolute'] && !$this->absoluteUrls) . $parts['fragment'];
	}


	/**
	 * Parse destination in format "[//] [[[module:]presenter:]action | signal! | this] [?query] [#fragment]"
	 * @throws InvalidLinkException
	 * @internal
	 */
	public static function parseDestination(string $destination): array
	{
		if (!preg_match('~^ (?<absolute>//)?+ (?<path>[^!?#]++) (?<signal>!)?+ (?<query>\?[^#]*)?+ (?<fragment>\#.*)?+ $~x', $destination, $matches)) {
			throw new InvalidLinkException("Invalid destination '$destination'.");
		}

		if (!empty($matches['query'])) {
			parse_str(substr($matches['query'], 1), $args);
		}

		return [
			'absolute' => (bool) $matches['absolute'],
			'path' => $matches['path'],
			'signal' => !empty($matches['signal']),
			'args' => $args ?? null,
			'fragment' => $matches['fragment'] ?? '',
		];
	}


	/**
	 * Converts Request to URL.
	 * @internal
	 */
	protected function requestToUrl(Application\Request $request, ?bool $relative = null): string
	{
		if (!isset($this->refUrlCache)) {
			$url = $this->httpRequest->getUrl();
			$this->refUrlCache = new Http\UrlScript($url->getHostUrl() . $url->getScriptPath());
		}

		if (empty($this->router)) {
			throw new Nette\InvalidStateException('Unable to generate URL, service Router has not been set.');
		}

		$url = $this->router->constructUrl($request->toArray(), $this->refUrlCache);
		if ($url === null) {
			$params = $request->getParameters();
			unset($params[self::ActionKey], $params[self::PresenterKey]);
			$params = urldecode(http_build_query($params, '', ', '));
			throw new InvalidLinkException("No route for {$request->getPresenterName()}:{$request->getParameter('action')}($params)");
		}

		if ($relative ?? !$this->absoluteUrls) {
			$hostUrl = $this->refUrlCache->getHostUrl() . '/';
			if (strncmp($url, $hostUrl, strlen($hostUrl)) === 0) {
				$url = substr($url, strlen($hostUrl) - 1);
			}
		}

		return $url;
	}


	/**
	 * Invalid link handler. Descendant can override this method to change default behaviour.
	 * @throws InvalidLinkException
	 */
	protected function handleInvalidLink(InvalidLinkException $e): string
	{
		if ($this->invalidLinkMode & self::InvalidLinkException) {
			throw $e;
		} elseif ($this->invalidLinkMode & self::InvalidLinkWarning) {
			trigger_error('Invalid link: ' . $e->getMessage(), E_USER_WARNING);
		}

		return $this->invalidLinkMode & self::InvalidLinkTextual
			? '#error: ' . $e->getMessage()
			: '#';
	}


	/********************* request serialization ****************d*g**/


	/**
	 * Stores current request to session.
	 */
	public function storeRequest(string $expiration = '+ 10 minutes'): string
	{
		$session = $this->getSession('Nette.Application/requests');
		do {
			$key = Nette\Utils\Random::generate(5);
		} while (isset($session[$key]));

		$session[$key] = [$this->user?->getId(), $this->request];
		$session->setExpiration($expiration, $key);
		return $key;
	}


	/**
	 * Restores request from session.
	 */
	public function restoreRequest(string $key): void
	{
		$session = $this->getSession('Nette.Application/requests');
		if (!isset($session[$key]) || ($session[$key][0] !== null && $session[$key][0] !== $this->getUser()->getId())) {
			return;
		}

		$request = clone $session[$key][1];
		unset($session[$key]);
		$params = $request->getParameters();
		$params[self::FlashKey] = $this->getFlashKey();
		$request->setParameters($params);
		if ($request->isMethod('POST')) {
			$request->setFlag(Application\Request::RESTORED, true);
			$this->sendResponse(new Responses\ForwardResponse($request));
		} else {
			$this->redirectUrl($this->requestToUrl($request));
		}
	}


	/********************* interface StatePersistent ****************d*g**/


	/**
	 * Descendant can override this method to return the names of custom persistent components.
	 * @return string[]
	 */
	public static function getPersistentComponents(): array
	{
		return [];
	}


	/**
	 * Saves state information for all subcomponents to $this->globalState.
	 */
	protected function getGlobalState(?string $forClass = null): array
	{
		$sinces = &$this->globalStateSinces;

		if (!isset($this->globalState)) {
			$state = [];
			foreach ($this->globalParams as $id => $params) {
				$prefix = $id . self::NameSeparator;
				foreach ($params as $key => $val) {
					$state[$prefix . $key] = $val;
				}
			}

			$this->saveStatePartial($state, new ComponentReflection($forClass ?? $this));

			if ($sinces === null) {
				$sinces = [];
				foreach ($this->getReflection()->getPersistentParams() as $name => $meta) {
					$sinces[$name] = $meta['since'];
				}
			}

			$persistents = $this->getReflection()->getPersistentComponents();

			foreach ($this->getComponentTree() as $component) {
				if ($component->getParent() === $this) {
					// counts on child-first search
					$since = $persistents[$component->getName()]['since'] ?? false; // false = nonpersistent
				}

				if (!$component instanceof StatePersistent) {
					continue;
				}

				$prefix = $component->getUniqueId() . self::NameSeparator;
				$params = [];
				$component->saveState($params);
				foreach ($params as $key => $val) {
					$state[$prefix . $key] = $val;
					$sinces[$prefix . $key] = $since;
				}
			}
		} else {
			$state = $this->globalState;
		}

		if ($forClass !== null) {
			$tree = Helpers::getClassesAndTraits($forClass);
			$since = null;
			foreach ($state as $key => $foo) {
				if (!isset($sinces[$key])) {
					$x = strpos($key, self::NameSeparator);
					$x = $x === false ? $key : substr($key, 0, $x);
					$sinces[$key] = $sinces[$x] ?? false;
				}

				if ($since !== $sinces[$key]) {
					$since = $sinces[$key];
					$ok = $since && isset($tree[$since]);
				}

				if (!$ok) {
					unset($state[$key]);
				}
			}
		}

		return $state;
	}


	/**
	 * Permanently saves state information for all subcomponents to $this->globalState.
	 */
	protected function saveGlobalState(): void
	{
		$this->globalParams = [];
		$this->globalState = $this->getGlobalState();
	}


	/**
	 * Initializes $this->globalParams, $this->signal & $this->signalReceiver, $this->action, $this->view. Called by run().
	 * @throws Nette\Application\BadRequestException if action name is not valid
	 */
	private function initGlobalParameters(): void
	{
		// init $this->globalParams
		$this->globalParams = [];
		$selfParams = [];

		$params = $this->request->getParameters();
		if (($tmp = $this->request->getPost('_' . self::SignalKey)) !== null) {
			$params[self::SignalKey] = $tmp;
		} elseif ($this->isAjax()) {
			$params += $this->request->getPost();
			if (($tmp = $this->request->getPost(self::SignalKey)) !== null) {
				$params[self::SignalKey] = $tmp;
			}
		}

		foreach ($params as $key => $value) {
			if (!preg_match('#^((?:[a-z0-9_]+-)*)((?!\d+$)[a-z0-9_]+)$#Di', (string) $key, $matches)) {
				continue;
			} elseif (!$matches[1]) {
				$selfParams[$key] = $value;
			} else {
				$this->globalParams[substr($matches[1], 0, -1)][$matches[2]] = $value;
			}
		}

		// init & validate $this->action & $this->view
		$action = $selfParams[self::ActionKey] ?? self::DefaultAction;
		if (!is_string($action) || !Nette\Utils\Strings::match($action, '#^[a-zA-Z0-9][a-zA-Z0-9_\x7f-\xff]*$#D')) {
			$this->error('Action name is not valid.');
		}

		$this->changeAction($action);
		$this->forwarded = false;

		// init $this->signalReceiver and key 'signal' in appropriate params array
		$this->signalReceiver = $this->getUniqueId();
		if (isset($selfParams[self::SignalKey])) {
			$param = $selfParams[self::SignalKey];
			if (!is_string($param)) {
				$this->error('Signal name is not string.');
			}

			$pos = strrpos($param, '-');
			if ($pos) {
				$this->signalReceiver = substr($param, 0, $pos);
				$this->signal = substr($param, $pos + 1);
			} else {
				$this->signalReceiver = $this->getUniqueId();
				$this->signal = $param;
			}

			if ($this->signal === '') {
				$this->signal = null;
			}
		}

		$this->loadState($selfParams);
	}


	/**
	 * Pops parameters for specified component.
	 * @internal
	 */
	final public function popGlobalParameters(string $id): array
	{
		$res = $this->globalParams[$id] ?? [];
		unset($this->globalParams[$id]);
		return $res;
	}


	/********************* flash session ****************d*g**/


	private function getFlashKey(): ?string
	{
		$flashKey = $this->getParameter(self::FlashKey);
		return is_string($flashKey) && $flashKey !== ''
			? $flashKey
			: null;
	}


	/**
	 * Checks if a flash session namespace exists.
	 */
	public function hasFlashSession(): bool
	{
		$flashKey = $this->getFlashKey();
		return $flashKey !== null
			&& $this->getSession()->hasSection('Nette.Application.Flash/' . $flashKey);
	}


	/**
	 * Returns session namespace provided to pass temporary data between redirects.
	 */
	public function getFlashSession(): Http\SessionSection
	{
		$flashKey = $this->getFlashKey();
		if ($flashKey === null) {
			$this->params[self::FlashKey] = $flashKey = Nette\Utils\Random::generate(4);
		}

		return $this->getSession('Nette.Application.Flash/' . $flashKey);
	}


	/********************* services ****************d*g**/


	final public function injectPrimary(
		Http\IRequest $httpRequest,
		Http\IResponse $httpResponse,
		?Application\IPresenterFactory $presenterFactory = null,
		?Nette\Routing\Router $router = null,
		?Http\Session $session = null,
		?Nette\Security\User $user = null,
		?TemplateFactory $templateFactory = null,
	): void
	{
		$this->presenterFactory = $presenterFactory;
		$this->router = $router;
		$this->httpRequest = $httpRequest;
		$this->httpResponse = $httpResponse;
		$this->session = $session;
		$this->user = $user;
		$this->templateFactory = $templateFactory;
	}


	final public function getHttpRequest(): Http\IRequest
	{
		return $this->httpRequest;
	}


	final public function getHttpResponse(): Http\IResponse
	{
		return $this->httpResponse;
	}


	final public function getSession(?string $namespace = null): Http\Session|Http\SessionSection
	{
		if (empty($this->session)) {
			throw new Nette\InvalidStateException('Service Session has not been set.');
		}

		return $namespace === null
			? $this->session
			: $this->session->getSection($namespace);
	}


	final public function getUser(): Nette\Security\User
	{
		return $this->user ?? throw new Nette\InvalidStateException('Service User has not been set.');
	}


	final public function getTemplateFactory(): TemplateFactory
	{
		return $this->templateFactory ?? throw new Nette\InvalidStateException('Service TemplateFactory has not been set.');
	}
}
