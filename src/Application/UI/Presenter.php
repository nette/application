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
 * @property-read Nette\DI\Container $context
 * @property-read Nette\Http\Session $session
 * @property-read Nette\Security\User $user
 */
abstract class Presenter extends Control implements Application\IPresenter
{
	/** bad link handling {@link Presenter::$invalidLinkMode} */
	public const
		INVALID_LINK_SILENT = 0b0000,
		INVALID_LINK_WARNING = 0b0001,
		INVALID_LINK_EXCEPTION = 0b0010,
		INVALID_LINK_TEXTUAL = 0b0100;

	/** @internal special parameter key */
	public const
		PRESENTER_KEY = 'presenter',
		SIGNAL_KEY = 'do',
		ACTION_KEY = 'action',
		FLASH_KEY = '_fid',
		DEFAULT_ACTION = 'default';

	/** @var int */
	public $invalidLinkMode;

	/** @var array<callable(self): void>  Occurs when the presenter is starting */
	public $onStartup = [];

	/** @var array<callable(self): void>  Occurs when the presenter is rendering after beforeRender */
	public $onRender = [];

	/** @var array<callable(self, Application\Response): void>  Occurs when the presenter is shutting down */
	public $onShutdown = [];

	/** @var bool  automatically call canonicalize() */
	public $autoCanonicalize = true;

	/** @var bool  use absolute Urls or paths? */
	public $absoluteUrls = false;

	/** @var Nette\Application\Request|null */
	private $request;

	/** @var Nette\Application\Response */
	private $response;

	/** @var array */
	private $globalParams = [];

	/** @var array */
	private $globalState;

	/** @var array|null */
	private $globalStateSinces;

	/** @var string */
	private $action;

	/** @var string */
	private $view;

	/** @var string|bool */
	private $layout;

	/** @var \stdClass */
	private $payload;

	/** @var string */
	private $signalReceiver;

	/** @var string|null */
	private $signal;

	/** @var bool */
	private $ajaxMode;

	/** @var bool */
	private $startupCheck;

	/** @var Nette\Application\Request|null */
	private $lastCreatedRequest;

	/** @var array|null */
	private $lastCreatedRequestFlag;

	/** @var Nette\DI\Container */
	private $context;

	/** @var Nette\Http\IRequest */
	private $httpRequest;

	/** @var Nette\Http\IResponse */
	private $httpResponse;

	/** @var Nette\Http\Session */
	private $session;

	/** @var Nette\Application\IPresenterFactory */
	private $presenterFactory;

	/** @var Nette\Routing\Router */
	private $router;

	/** @var Nette\Security\User */
	private $user;

	/** @var TemplateFactory */
	private $templateFactory;

	/** @var Nette\Http\UrlScript */
	private $refUrlCache;


	public function __construct()
	{
		$this->payload = new \stdClass;
	}


	final public function getRequest(): ?Application\Request
	{
		return $this->request;
	}


	/**
	 * Returns self.
	 */
	final public function getPresenter(): self
	{
		return $this;
	}


	final public function getPresenterIfExists(): self
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
		return Nette\Utils\Strings::startsWith($current . ':', ltrim($module . ':', ':'));
	}


	/********************* interface IPresenter ****************d*g**/


	public function run(Application\Request $request): Application\Response
	{
		try {
			// STARTUP
			$this->request = $request;
			$this->payload = $this->payload ?: new \stdClass;
			$this->setParent($this->getParent(), $request->getPresenterName());

			if (!$this->httpResponse->isSent()) {
				$this->httpResponse->addHeader('Vary', 'X-Requested-With');
			}

			$this->initGlobalParameters();
			$this->checkRequirements(static::getReflection());
			Arrays::invoke($this->onStartup, $this);
			$this->startup();
			if (!$this->startupCheck) {
				$class = static::getReflection()->getMethod('startup')->getDeclaringClass()->getName();
				throw new Nette\InvalidStateException("Method $class::startup() or its descendant doesn't call parent::startup().");
			}
			// calls $this->action<Action>()
			$this->tryCall(static::formatActionMethod($this->action), $this->params);

			// autoload components
			foreach ($this->globalParams as $id => $foo) {
				$this->getComponent((string) $id, false);
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
			$this->tryCall(static::formatRenderMethod($this->view), $this->params);
			$this->afterRender();

			// save component tree persistent state
			$this->saveGlobalState();
			if ($this->isAjax()) {
				$this->payload->state = $this->getGlobalState();
			}

			// finish template rendering
			$this->sendTemplate();

		} catch (Application\AbortException $e) {
		}

		if ($this->isAjax()) {
			try {
				$hasPayload = (array) $this->payload;
				unset($hasPayload['state']);
				if ($this->response instanceof Responses\TextResponse && $this->isControlInvalid()) {
					$this->snippetMode = true;
					$this->response->send($this->httpRequest, $this->httpResponse);
					$this->sendPayload();
				}
			} catch (Application\AbortException $e) {
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
	 * @return void
	 */
	protected function afterRender()
	{
	}


	/**
	 * @return void
	 */
	protected function shutdown(Application\Response $response)
	{
	}


	/**
	 * This method will be called when CSRF is detected.
	 */
	public function detectedCsrf(): void
	{
		$this->redirect('this');
	}


	/********************* signal handling ****************d*g**/


	/**
	 * @throws BadSignalException
	 */
	public function processSignal(): void
	{
		if ($this->signal === null) {
			return;
		}

		$component = $this->signalReceiver === ''
			? $this
			: $this->getComponent($this->signalReceiver, false);
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
	 * @param  Component|string  $component
	 */
	final public function isSignalReceiver($component, $signal = null): bool
	{
		if ($component instanceof Nette\ComponentModel\Component) {
			$component = $component === $this
				? ''
				: $component->lookupPath(self::class, true);
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
		$this->action = $this->view = $action;
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
	 * @return static
	 */
	public function setView(string $view)
	{
		$this->view = $view;
		return $this;
	}


	/**
	 * Returns current layout name.
	 * @return string|bool
	 */
	final public function getLayout()
	{
		return $this->layout;
	}


	/**
	 * Changes or disables layout.
	 * @param  string|bool  $layout
	 * @return static
	 */
	public function setLayout($layout)
	{
		$this->layout = $layout === false ? false : (string) $layout;
		return $this;
	}


	/**
	 * @throws Nette\Application\AbortException
	 */
	public function sendTemplate(Template $template = null): void
	{
		$template = $template ?? $this->getTemplate();
		if (!$template->getFile()) {
			$files = $this->formatTemplateFiles();
			foreach ($files as $file) {
				if (is_file($file)) {
					$template->setFile($file);
					break;
				}
			}

			if (!$template->getFile()) {
				$file = strtr(Arrays::first($files), '/', DIRECTORY_SEPARATOR);
				$this->error("Page not found. Missing template '$file'.");
			}
		}

		$this->sendResponse(new Responses\TextResponse($template));
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
		return 'action' . $action;
	}


	/**
	 * Formats render view method name.
	 */
	public static function formatRenderMethod(string $view): string
	{
		return 'render' . $view;
	}


	/**
	 * @param  string  $class
	 */
	protected function createTemplate(/*string $class = null*/): Template
	{
		$class = func_num_args() // back compatibility
			? func_get_arg(0)
			: $this->formatTemplateClass();
		return $this->getTemplateFactory()->createTemplate($this, $class);
	}


	public function formatTemplateClass(): ?string
	{
		$class = preg_replace('#Presenter$#', ucfirst((string) $this->action) . 'Template', static::class);
		if (class_exists($class) && is_a($class, Template::class, true)) {
			return $class;
		}
		return parent::formatTemplateClass();
	}


	/********************* partial AJAX rendering ****************d*g**/


	final public function getPayload(): \stdClass
	{
		return $this->payload;
	}


	/**
	 * Is AJAX request?
	 */
	public function isAjax(): bool
	{
		if ($this->ajaxMode === null) {
			$this->ajaxMode = $this->httpRequest->isAjax();
		}
		return $this->ajaxMode;
	}


	/**
	 * Sends AJAX payload to the output.
	 * @throws Nette\Application\AbortException
	 */
	public function sendPayload(): void
	{
		$this->sendResponse(new Responses\JsonResponse($this->payload));
	}


	/**
	 * Sends JSON data to the output.
	 * @param  mixed  $data
	 * @throws Nette\Application\AbortException
	 */
	public function sendJson($data): void
	{
		$this->sendResponse(new Responses\JsonResponse($data));
	}


	/********************* navigation & flow ****************d*g**/


	/**
	 * Sends response and terminates presenter.
	 * @throws Nette\Application\AbortException
	 */
	public function sendResponse(Application\Response $response): void
	{
		$this->response = $response;
		$this->terminate();
	}


	/**
	 * Correctly terminates presenter.
	 * @throws Nette\Application\AbortException
	 */
	public function terminate(): void
	{
		throw new Application\AbortException;
	}


	/**
	 * Forward to another presenter or action.
	 * @param  string|Nette\Application\Request  $destination
	 * @param  array|mixed  $args
	 * @throws Nette\Application\AbortException
	 */
	public function forward($destination, $args = []): void
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
	 */
	public function redirectUrl(string $url, int $httpCode = null): void
	{
		if ($this->isAjax()) {
			$this->payload->redirect = $url;
			$this->sendPayload();

		} elseif (!$httpCode) {
			$httpCode = $this->httpRequest->isMethod('post')
				? Http\IResponse::S303_POST_GET
				: Http\IResponse::S302_FOUND;
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
	 * @throws Nette\Application\AbortException
	 */
	public function canonicalize(string $destination = null, array $args = []): void
	{
		$request = $this->request;
		if ($this->isAjax() || (!$request->isMethod('get') && !$request->isMethod('head'))) {
			return;
		}

		try {
			$url = $this->createRequest(
				$this,
				$destination ?: $this->action,
				$args + $this->getGlobalState() + $request->getParameters(),
				'redirectX'
			);
		} catch (InvalidLinkException $e) {
		}
		if (!isset($url) || $this->httpRequest->getUrl()->withoutUserInfo()->isEqual($url)) {
			return;
		}

		$code = $request->hasFlag($request::VARYING)
			? Http\IResponse::S302_FOUND
			: Http\IResponse::S301_MOVED_PERMANENTLY;
		$this->sendResponse(new Responses\RedirectResponse($url, $code));
	}


	/**
	 * Attempts to cache the sent entity by its last modification date.
	 * @param  string|int|\DateTimeInterface  $lastModified
	 * @param  string  $etag  strong entity tag validator
	 * @param  string  $expire  like '20 minutes'
	 * @throws Nette\Application\AbortException
	 */
	public function lastModified($lastModified, string $etag = null, string $expire = null): void
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
	 * @return string|null   URL
	 * @throws InvalidLinkException
	 * @internal
	 */
	protected function createRequest(
		Component $component,
		string $destination,
		array $args,
		string $mode
	): ?string {
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
			if (!$this->presenterFactory) {
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
			$reflection = new ComponentReflection(get_class($component));
			if ($signal === 'this') { // means "no signal"
				$signal = '';
				if (array_key_exists(0, $args)) {
					throw new InvalidLinkException("Unable to pass parameters to 'this!' signal.");
				}

			} elseif (strpos($signal, self::NAME_SEPARATOR) === false) {
				// counterpart of signalReceived() & tryCall()
				$method = $component->formatSignalMethod($signal);
				if (!$reflection->hasCallableMethod($method)) {
					throw new InvalidLinkException("Unknown signal '$signal', missing handler {$reflection->getName()}::$method()");
				}
				// convert indexed parameters to named
				static::argsToParams(get_class($component), $method, $args, [], $missing);
			}

			// counterpart of StatePersistent
			if ($args && array_intersect_key($args, $reflection->getPersistentParams())) {
				$component->saveState($args);
			}

			if ($args && $component !== $this) {
				$prefix = $component->getUniqueId() . self::NAME_SEPARATOR;
				foreach ($args as $key => $val) {
					unset($args[$key]);
					$args[$prefix . $key] = $val;
				}
			}
		}

		// PROCESS ARGUMENTS
		if (is_subclass_of($presenterClass, self::class)) {
			if ($action === '') {
				$action = self::DEFAULT_ACTION;
			}

			$current = ($action === '*' || strcasecmp($action, (string) $this->action) === 0) && $presenterClass === static::class;

			$reflection = new ComponentReflection($presenterClass);

			// counterpart of run() & tryCall()
			$method = $presenterClass::formatActionMethod($action);
			if (!$reflection->hasCallableMethod($method)) {
				$method = $presenterClass::formatRenderMethod($action);
				if (!$reflection->hasCallableMethod($method)) {
					$method = null;
				}
			}

			// convert indexed parameters to named
			if ($method === null) {
				if (array_key_exists(0, $args)) {
					throw new InvalidLinkException("Unable to pass parameters to action '$presenter:$action', missing corresponding method.");
				}
			} else {
				static::argsToParams($presenterClass, $method, $args, $path === 'this' ? $this->params : [], $missing);
			}

			// counterpart of StatePersistent
			if ($args && array_intersect_key($args, $reflection->getPersistentParams())) {
				$this->saveState($args, $reflection);
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
			$args[self::ACTION_KEY] = $action;
		}
		if (!empty($signal)) {
			$args[self::SIGNAL_KEY] = $component->getParameterId($signal);
			$current = $current && $args[self::SIGNAL_KEY] === $this->getParameter(self::SIGNAL_KEY);
		}
		if (($mode === 'redirect' || $mode === 'forward') && $this->hasFlashSession()) {
			$args[self::FLASH_KEY] = $this->getFlashKey();
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
	protected function requestToUrl(Application\Request $request, bool $relative = null): string
	{
		if ($this->refUrlCache === null) {
			$url = $this->httpRequest->getUrl();
			$this->refUrlCache = new Http\UrlScript($url->withoutUserInfo()->getHostUrl() . $url->getScriptPath());
		}
		if (!$this->router) {
			throw new Nette\InvalidStateException('Unable to generate URL, service Router has not been set.');
		}

		$url = $this->router->constructUrl($request->toArray(), $this->refUrlCache);
		if ($url === null) {
			$params = $request->getParameters();
			unset($params[self::ACTION_KEY], $params[self::PRESENTER_KEY]);
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
	 * Converts list of arguments to named parameters.
	 * @param  \ReflectionParameter[]  $missing arguments
	 * @throws InvalidLinkException
	 * @internal
	 */
	public static function argsToParams(
		string $class,
		string $method,
		array &$args,
		array $supplemental = [],
		array &$missing = null
	): void {
		$i = 0;
		$rm = new \ReflectionMethod($class, $method);
		foreach ($rm->getParameters() as $param) {
			$type = ComponentReflection::getParameterType($param);
			$name = $param->getName();

			if (array_key_exists($i, $args)) {
				$args[$name] = $args[$i];
				unset($args[$i]);
				$i++;

			} elseif (array_key_exists($name, $args)) {
				// continue with process

			} elseif (array_key_exists($name, $supplemental)) {
				$args[$name] = $supplemental[$name];
			}

			if (!isset($args[$name])) {
				if (
					!$param->isDefaultValueAvailable()
					&& !$param->allowsNull()
					&& $type !== 'scalar'
					&& $type !== 'array'
					&& $type !== 'iterable'
				) {
					$missing[] = $param;
					unset($args[$name]);
				}
				continue;
			}

			if (!ComponentReflection::convertType($args[$name], $type)) {
				throw new InvalidLinkException(sprintf(
					'Argument $%s passed to %s() must be %s, %s given.',
					$name,
					$rm->getDeclaringClass()->getName() . '::' . $rm->getName(),
					$type,
					is_object($args[$name]) ? get_class($args[$name]) : gettype($args[$name])
				));
			}

			$def = $param->isDefaultValueAvailable()
				? $param->getDefaultValue()
				: null;
			if ($args[$name] === $def || ($def === null && $args[$name] === '')) {
				$args[$name] = null; // value transmit is unnecessary
			}
		}

		if (array_key_exists($i, $args)) {
			throw new InvalidLinkException("Passed more parameters than method $class::{$rm->getName()}() expects.");
		}
	}


	/**
	 * Invalid link handler. Descendant can override this method to change default behaviour.
	 * @throws InvalidLinkException
	 */
	protected function handleInvalidLink(InvalidLinkException $e): string
	{
		if ($this->invalidLinkMode & self::INVALID_LINK_EXCEPTION) {
			throw $e;
		} elseif ($this->invalidLinkMode & self::INVALID_LINK_WARNING) {
			trigger_error('Invalid link: ' . $e->getMessage(), E_USER_WARNING);
		}
		return $this->invalidLinkMode & self::INVALID_LINK_TEXTUAL
			? '#error: ' . $e->getMessage()
			: '#';
	}


	/********************* request serialization ****************d*g**/


	/**
	 * Stores current request to session.
	 * @return string key
	 */
	public function storeRequest(string $expiration = '+ 10 minutes'): string
	{
		$session = $this->getSession('Nette.Application/requests');
		do {
			$key = Nette\Utils\Random::generate(5);
		} while (isset($session[$key]));

		$session[$key] = [$this->user ? $this->user->getId() : null, $this->request];
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
		$params[self::FLASH_KEY] = $this->getFlashKey();
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
	 * Returns array of persistent components.
	 * This default implementation detects components by class-level annotation @persistent(cmp1, cmp2).
	 */
	public static function getPersistentComponents(): array
	{
		$rc = new \ReflectionClass(static::class);
		$attrs = PHP_VERSION_ID >= 80000
			? $rc->getAttributes(Application\Attributes\Persistent::class)
			: null;
		return $attrs
			? $attrs[0]->getArguments()
			: (array) ComponentReflection::parseAnnotation($rc, 'persistent');
	}


	/**
	 * Saves state information for all subcomponents to $this->globalState.
	 */
	protected function getGlobalState(string $forClass = null): array
	{
		$sinces = &$this->globalStateSinces;

		if ($this->globalState === null) {
			$state = [];
			foreach ($this->globalParams as $id => $params) {
				$prefix = $id . self::NAME_SEPARATOR;
				foreach ($params as $key => $val) {
					$state[$prefix . $key] = $val;
				}
			}
			$this->saveState($state, $forClass ? new ComponentReflection($forClass) : null);

			if ($sinces === null) {
				$sinces = [];
				foreach ($this->getReflection()->getPersistentParams() as $name => $meta) {
					$sinces[$name] = $meta['since'];
				}
			}

			$components = $this->getReflection()->getPersistentComponents();
			$iterator = $this->getComponents(true);

			foreach ($iterator as $name => $component) {
				if ($iterator->getDepth() === 0) {
					// counts with Nette\Application\RecursiveIteratorIterator::SELF_FIRST
					$since = $components[$name]['since'] ?? false; // false = nonpersistent
				}
				if (!$component instanceof StatePersistent) {
					continue;
				}
				$prefix = $component->getUniqueId() . self::NAME_SEPARATOR;
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
			$tree = ComponentReflection::getClassesAndTraits($forClass);
			$since = null;
			foreach ($state as $key => $foo) {
				if (!isset($sinces[$key])) {
					$x = strpos($key, self::NAME_SEPARATOR);
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
	 * Saves state informations for next request.
	 */
	public function saveState(array &$params, ComponentReflection $reflection = null): void
	{
		($reflection ?: static::getReflection())->saveState($this, $params);
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
		if (($tmp = $this->request->getPost('_' . self::SIGNAL_KEY)) !== null) {
			$params[self::SIGNAL_KEY] = $tmp;
		} elseif ($this->isAjax()) {
			$params += $this->request->getPost();
			if (($tmp = $this->request->getPost(self::SIGNAL_KEY)) !== null) {
				$params[self::SIGNAL_KEY] = $tmp;
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
		$action = $selfParams[self::ACTION_KEY] ?? self::DEFAULT_ACTION;
		if (!is_string($action) || !Nette\Utils\Strings::match($action, '#^[a-zA-Z0-9][a-zA-Z0-9_\x7f-\xff]*$#D')) {
			$this->error('Action name is not valid.');
		}
		$this->changeAction($action);

		// init $this->signalReceiver and key 'signal' in appropriate params array
		$this->signalReceiver = $this->getUniqueId();
		if (isset($selfParams[self::SIGNAL_KEY])) {
			$param = $selfParams[self::SIGNAL_KEY];
			if (!is_string($param)) {
				$this->error('Signal name is not string.');
			}
			$pos = strrpos($param, '-');
			if ($pos) {
				$this->signalReceiver = substr($param, 0, $pos);
				$this->signal = (string) substr($param, $pos + 1);
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
		$flashKey = $this->getParameter(self::FLASH_KEY);
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
			$this->params[self::FLASH_KEY] = $flashKey = Nette\Utils\Random::generate(4);
		}
		return $this->getSession('Nette.Application.Flash/' . $flashKey);
	}


	/********************* services ****************d*g**/


	final public function injectPrimary(
		?Nette\DI\Container $context,
		?Application\IPresenterFactory $presenterFactory,
		?Nette\Routing\Router $router,
		Http\IRequest $httpRequest,
		Http\IResponse $httpResponse,
		?Http\Session $session = null,
		?Nette\Security\User $user = null,
		?TemplateFactory $templateFactory = null
	) {
		if ($this->presenterFactory !== null) {
			throw new Nette\InvalidStateException('Method ' . __METHOD__ . ' is intended for initialization and should not be called more than once.');
		}

		$this->context = $context;
		$this->presenterFactory = $presenterFactory;
		$this->router = $router;
		$this->httpRequest = $httpRequest;
		$this->httpResponse = $httpResponse;
		$this->session = $session;
		$this->user = $user;
		$this->templateFactory = $templateFactory;
	}


	/**
	 * Gets the context.
	 * @deprecated
	 */
	public function getContext(): Nette\DI\Container
	{
		if (!$this->context) {
			throw new Nette\InvalidStateException('Context has not been set.');
		}
		trigger_error(__METHOD__ . '() is deprecated, use dependency injection.', E_USER_DEPRECATED);
		return $this->context;
	}


	final public function getHttpRequest(): Http\IRequest
	{
		return $this->httpRequest;
	}


	final public function getHttpResponse(): Http\IResponse
	{
		return $this->httpResponse;
	}


	/**
	 * @return Http\Session|Http\SessionSection
	 */
	final public function getSession(string $namespace = null)
	{
		if (!$this->session) {
			throw new Nette\InvalidStateException('Service Session has not been set.');
		}
		return $namespace === null
			? $this->session
			: $this->session->getSection($namespace);
	}


	final public function getUser(): Nette\Security\User
	{
		if (!$this->user) {
			throw new Nette\InvalidStateException('Service User has not been set.');
		}
		return $this->user;
	}


	final public function getTemplateFactory(): TemplateFactory
	{
		if (!$this->templateFactory) {
			throw new Nette\InvalidStateException('Service TemplateFactory has not been set.');
		}
		return $this->templateFactory;
	}
}
