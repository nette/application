<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\UI;

use Nette;
use Nette\Application;
use Nette\Application\Responses;
use Nette\Application\Helpers;
use Nette\Http;


/**
 * Presenter component represents a webpage instance. It converts Request to IResponse.
 *
 * @property-read Nette\Application\Request $request
 * @property-read string $action
 * @property      string $view
 * @property      string $layout
 * @property-read \stdClass $payload
 * @property-read Nette\DI\Container $context
 * @property-read Nette\Http\Session $session
 * @property-read Nette\Security\User $user
 */
abstract class Presenter extends Control implements Application\IPresenter
{
	/** bad link handling {@link Presenter::$invalidLinkMode} */
	const INVALID_LINK_SILENT = 0b0000,
		INVALID_LINK_WARNING = 0b0001,
		INVALID_LINK_EXCEPTION = 0b0010,
		INVALID_LINK_TEXTUAL = 0b0100;

	/** @internal special parameter key */
	const SIGNAL_KEY = 'do',
		ACTION_KEY = 'action',
		FLASH_KEY = '_fid',
		DEFAULT_ACTION = 'default';

	/** @var int */
	public $invalidLinkMode;

	/** @var callable[]  function (Presenter $sender, IResponse $response = NULL); Occurs when the presenter is shutting down */
	public $onShutdown;

	/** @var Nette\Application\Request|NULL */
	private $request;

	/** @var Nette\Application\IResponse */
	private $response;

	/** @var bool  automatically call canonicalize() */
	public $autoCanonicalize = TRUE;

	/** @var bool  use absolute Urls or paths? */
	public $absoluteUrls = FALSE;

	/** @var array */
	private $globalParams;

	/** @var array */
	private $globalState;

	/** @var array */
	private $globalStateSinces;

	/** @var string */
	private $action;

	/** @var string */
	private $view;

	/** @var string */
	private $layout;

	/** @var \stdClass */
	private $payload;

	/** @var string */
	private $signalReceiver;

	/** @var string */
	private $signal;

	/** @var bool */
	private $ajaxMode;

	/** @var bool */
	private $startupCheck;

	/** @var Nette\Application\Request|NULL */
	private $lastCreatedRequest;

	/** @var array */
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

	/** @var Nette\Application\IRouter */
	private $router;

	/** @var Nette\Security\User */
	private $user;

	/** @var ITemplateFactory */
	private $templateFactory;

	/** @var Nette\Http\Url */
	private $refUrlCache;


	public function __construct()
	{
		$this->payload = new \stdClass;
	}


	public function getRequest(): ?Application\Request
	{
		return $this->request;
	}


	/**
	 * Returns self.
	 */
	public function getPresenter(bool $throw = TRUE): Presenter
	{
		return $this;
	}


	/**
	 * Returns a name that uniquely identifies component.
	 */
	public function getUniqueId(): string
	{
		return '';
	}


	/********************* interface IPresenter ****************d*g**/


	public function run(Application\Request $request): Application\IResponse
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
			$this->checkRequirements($this->getReflection());
			$this->startup();
			if (!$this->startupCheck) {
				$class = $this->getReflection()->getMethod('startup')->getDeclaringClass()->getName();
				throw new Nette\InvalidStateException("Method $class::startup() or its descendant doesn't call parent::startup().");
			}
			// calls $this->action<Action>()
			$this->tryCall($this->formatActionMethod($this->action), $this->params);

			// autoload components
			foreach ($this->globalParams as $id => $foo) {
				$this->getComponent($id, FALSE);
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
			// calls $this->render<View>()
			$this->tryCall($this->formatRenderMethod($this->view), $this->params);
			$this->afterRender();

			// save component tree persistent state
			$this->saveGlobalState();
			if ($this->isAjax()) {
				$this->payload->state = $this->getGlobalState();
			}

			// finish template rendering
			if ($this->getTemplate()) {
				$this->sendTemplate();
			}

		} catch (Application\AbortException $e) {
		}

		if ($this->isAjax()) {
			try {
				$hasPayload = (array) $this->payload;
				unset($hasPayload['state']);
				if ($this->response instanceof Responses\TextResponse && $this->isControlInvalid()) {
					$this->snippetMode = TRUE;
					$this->response->send($this->httpRequest, $this->httpResponse);
					$this->sendPayload();
				}
			} catch (Application\AbortException $e) {
			}
		}

		if ($this->hasFlashSession()) {
			$this->getFlashSession()->setExpiration($this->response instanceof Responses\RedirectResponse ? '+ 30 seconds' : '+ 3 seconds');
		}

		if (!$this->response) {
			$this->response = new Responses\VoidResponse;
		}

		$this->onShutdown($this, $this->response);
		$this->shutdown($this->response);

		return $this->response;
	}


	/**
	 * @return void
	 */
	protected function startup()
	{
		$this->startupCheck = TRUE;
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
	protected function shutdown(Application\IResponse $response)
	{
	}


	/**
	 * Checks authorization.
	 */
	public function checkRequirements($element): void
	{
		$user = (array) ComponentReflection::parseAnnotation($element, 'User');
		if (in_array('loggedIn', $user, TRUE) && !$this->getUser()->isLoggedIn()) {
			throw new Application\ForbiddenRequestException;
		}
	}


	/********************* signal handling ****************d*g**/


	/**
	 * @throws BadSignalException
	 */
	public function processSignal(): void
	{
		if ($this->signal === NULL) {
			return;
		}

		$component = $this->signalReceiver === '' ? $this : $this->getComponent($this->signalReceiver, FALSE);
		if ($component === NULL) {
			throw new BadSignalException("The signal receiver component '$this->signalReceiver' is not found.");

		} elseif (!$component instanceof ISignalReceiver) {
			throw new BadSignalException("The signal receiver component '$this->signalReceiver' is not ISignalReceiver implementor.");
		}

		$component->signalReceived($this->signal);
		$this->signal = NULL;
	}


	/**
	 * Returns pair signal receiver and name.
	 */
	public function getSignal(): ?array
	{
		return $this->signal === NULL ? NULL : [$this->signalReceiver, $this->signal];
	}


	/**
	 * Checks if the signal receiver is the given one.
	 * @param  mixed  component or its id
	 * @param  string signal name (optional)
	 */
	public function isSignalReceiver($component, string $signal = NULL): bool
	{
		if ($component instanceof Nette\ComponentModel\Component) {
			$component = $component === $this ? '' : $component->lookupPath(__CLASS__, TRUE);
		}

		if ($this->signal === NULL) {
			return FALSE;

		} elseif ($signal === TRUE) {
			return $component === ''
				|| strncmp($this->signalReceiver . '-', $component . '-', strlen($component) + 1) === 0;

		} elseif ($signal === NULL) {
			return $this->signalReceiver === $component;

		} else {
			return $this->signalReceiver === $component && strcasecmp($signal, $this->signal) === 0;
		}
	}


	/********************* rendering ****************d*g**/


	/**
	 * Returns current action name.
	 */
	public function getAction(bool $fullyQualified = FALSE): string
	{
		return $fullyQualified ? ':' . $this->getName() . ':' . $this->action : $this->action;
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
	public function getView(): string
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
	 * @return string|FALSE
	 */
	public function getLayout()
	{
		return $this->layout;
	}


	/**
	 * Changes or disables layout.
	 * @param  string|FALSE
	 * @return static
	 */
	public function setLayout($layout)
	{
		$this->layout = $layout === FALSE ? FALSE : (string) $layout;
		return $this;
	}


	/**
	 * @throws Nette\Application\BadRequestException if no template found
	 * @throws Nette\Application\AbortException
	 */
	public function sendTemplate(): void
	{
		$template = $this->getTemplate();
		if (!$template->getFile()) {
			$files = $this->formatTemplateFiles();
			foreach ($files as $file) {
				if (is_file($file)) {
					$template->setFile($file);
					break;
				}
			}

			if (!$template->getFile()) {
				$file = preg_replace('#^.*([/\\\\].{1,70})\z#U', "\u{2026}\$1", reset($files));
				$file = strtr($file, '/', DIRECTORY_SEPARATOR);
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
		if ($this->layout === FALSE) {
			return NULL;
		}
		$files = $this->formatLayoutTemplateFiles();
		foreach ($files as $file) {
			if (is_file($file)) {
				return $file;
			}
		}

		if ($this->layout) {
			$file = preg_replace('#^.*([/\\\\].{1,70})\z#U', "\u{2026}\$1", reset($files));
			$file = strtr($file, '/', DIRECTORY_SEPARATOR);
			throw new Nette\FileNotFoundException("Layout not found. Missing template '$file'.");
		}
		return NULL;
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
		$layout = $this->layout ? $this->layout : 'layout';
		$dir = dirname($this->getReflection()->getFileName());
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
		$dir = dirname($this->getReflection()->getFileName());
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


	protected function createTemplate(): ITemplate
	{
		return $this->getTemplateFactory()->createTemplate($this);
	}


	/********************* partial AJAX rendering ****************d*g**/


	public function getPayload(): \stdClass
	{
		return $this->payload;
	}


	/**
	 * Is AJAX request?
	 */
	public function isAjax(): bool
	{
		if ($this->ajaxMode === NULL) {
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
	 * @param  mixed
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
	public function sendResponse(Application\IResponse $response): void
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
		throw new Application\AbortException();
	}


	/**
	 * Forward to another presenter or action.
	 * @param  string|Nette\Application\Request
	 * @param  array|mixed
	 * @throws Nette\Application\AbortException
	 */
	public function forward($destination, $args = []): void
	{
		if ($destination instanceof Application\Request) {
			$this->sendResponse(new Responses\ForwardResponse($destination));
		}

		$args = func_num_args() < 3 && is_array($args) ? $args : array_slice(func_get_args(), 1);
		$this->createRequest($this, $destination, $args, 'forward');
		$this->sendResponse(new Responses\ForwardResponse($this->lastCreatedRequest));
	}


	/**
	 * Redirect to another URL and ends presenter execution.
	 * @throws Nette\Application\AbortException
	 */
	public function redirectUrl(string $url, int $httpCode = NULL): void
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
	 * Throws HTTP error.
	 * @throws Nette\Application\BadRequestException
	 */
	public function error(string $message = NULL, int $httpCode = Http\IResponse::S404_NOT_FOUND): void
	{
		throw new Application\BadRequestException((string) $message, (int) $httpCode);
	}


	/**
	 * Returns the last created Request.
	 * @internal
	 */
	public function getLastCreatedRequest(): ?Application\Request
	{
		return $this->lastCreatedRequest;
	}


	/**
	 * Returns the last created Request flag.
	 * @internal
	 */
	public function getLastCreatedRequestFlag(string $flag): bool
	{
		return !empty($this->lastCreatedRequestFlag[$flag]);
	}


	/**
	 * Conditional redirect to canonicalized URI.
	 * @throws Nette\Application\AbortException
	 */
	public function canonicalize(): void
	{
		if (!$this->isAjax() && ($this->request->isMethod('get') || $this->request->isMethod('head'))) {
			try {
				$url = $this->createRequest($this, $this->action, $this->getGlobalState() + $this->request->getParameters(), 'redirectX');
			} catch (InvalidLinkException $e) {
			}
			if (isset($url) && !$this->httpRequest->getUrl()->isEqual($url)) {
				$this->sendResponse(new Responses\RedirectResponse($url, Http\IResponse::S301_MOVED_PERMANENTLY));
			}
		}
	}


	/**
	 * Attempts to cache the sent entity by its last modification date.
	 * @param  string|int|\DateTimeInterface  last modified time
	 * @param  string strong entity tag validator
	 * @param  string like '20 minutes'
	 * @throws Nette\Application\AbortException
	 */
	public function lastModified($lastModified, string $etag = NULL, $expire = NULL): void
	{
		if ($expire !== NULL) {
			$this->httpResponse->setExpiration($expire);
		}
		$helper = new Http\Context($this->httpRequest, $this->httpResponse);
		if (!$helper->isModified($lastModified, $etag)) {
			$this->terminate();
		}
	}


	/**
	 * Request/URL factory.
	 * @param  Component  base
	 * @param  string   destination in format "[//] [[[module:]presenter:]action | signal! | this] [#fragment]"
	 * @param  array    array of arguments
	 * @param  string   forward|redirect|link
	 * @return string|NULL   URL
	 * @throws InvalidLinkException
	 * @internal
	 */
	protected function createRequest(Component $component, string $destination, array $args, string $mode): ?string
	{
		// note: createRequest supposes that saveState(), run() & tryCall() behaviour is final

		$this->lastCreatedRequest = $this->lastCreatedRequestFlag = NULL;

		// PARSE DESTINATION
		// 1) fragment
		$a = strpos($destination, '#');
		if ($a === FALSE) {
			$fragment = '';
		} else {
			$fragment = substr($destination, $a);
			$destination = substr($destination, 0, $a);
		}

		// 2) ?query syntax
		$a = strpos($destination, '?');
		if ($a !== FALSE) {
			parse_str(substr($destination, $a + 1), $args);
			$destination = substr($destination, 0, $a);
		}

		// 3) URL scheme
		$a = strpos($destination, '//');
		if ($a === FALSE) {
			$scheme = FALSE;
		} else {
			$scheme = substr($destination, 0, $a);
			$destination = substr($destination, $a + 2);
		}

		// 4) signal or empty
		if (!$component instanceof self || substr($destination, -1) === '!') {
			[$cname, $signal] = Helpers::splitName(rtrim($destination, '!'));
			if ($cname !== '') {
				$component = $component->getComponent(strtr($cname, ':', '-'));
			}
			if ($signal === '') {
				throw new InvalidLinkException('Signal must be non-empty string.');
			}
			$destination = 'this';
		}

		if ($destination == NULL) {  // intentionally ==
			throw new InvalidLinkException('Destination must be non-empty string.');
		}

		// 5) presenter: action
		$current = FALSE;
		[$presenter, $action] = Helpers::splitName($destination);
		if ($presenter === '') {
			$action = $destination === 'this' ? $this->action : $action;
			$presenter = $this->getName();
			$presenterClass = get_class($this);

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
		if (isset($signal)) { // $component must be IStatePersistent
			$reflection = new ComponentReflection(get_class($component));
			if ($signal === 'this') { // means "no signal"
				$signal = '';
				if (array_key_exists(0, $args)) {
					throw new InvalidLinkException("Unable to pass parameters to 'this!' signal.");
				}

			} elseif (strpos($signal, self::NAME_SEPARATOR) === FALSE) {
				// counterpart of signalReceived() & tryCall()
				$method = $component->formatSignalMethod($signal);
				if (!$reflection->hasCallableMethod($method)) {
					throw new InvalidLinkException("Unknown signal '$signal', missing handler {$reflection->getName()}::$method()");
				}
				// convert indexed parameters to named
				self::argsToParams(get_class($component), $method, $args, [], $missing);
			}

			// counterpart of IStatePersistent
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
		if (is_subclass_of($presenterClass, __CLASS__)) {
			if ($action === '') {
				$action = self::DEFAULT_ACTION;
			}

			$current = ($action === '*' || strcasecmp($action, (string) $this->action) === 0) && $presenterClass === get_class($this);

			$reflection = new ComponentReflection($presenterClass);

			// counterpart of run() & tryCall()
			$method = $presenterClass::formatActionMethod($action);
			if (!$reflection->hasCallableMethod($method)) {
				$method = $presenterClass::formatRenderMethod($action);
				if (!$reflection->hasCallableMethod($method)) {
					$method = NULL;
				}
			}

			// convert indexed parameters to named
			if ($method === NULL) {
				if (array_key_exists(0, $args)) {
					throw new InvalidLinkException("Unable to pass parameters to action '$presenter:$action', missing corresponding method.");
				}
			} else {
				self::argsToParams($presenterClass, $method, $args, $destination === 'this' ? $this->params : [], $missing);
			}

			// counterpart of IStatePersistent
			if ($args && array_intersect_key($args, $reflection->getPersistentParams())) {
				$this->saveState($args, $reflection);
			}

			if ($mode === 'redirect') {
				$this->saveGlobalState();
			}

			$globalState = $this->getGlobalState($destination === 'this' ? NULL : $presenterClass);
			if ($current && $args) {
				$tmp = $globalState + $this->params;
				foreach ($args as $key => $val) {
					if (http_build_query([$val]) !== (isset($tmp[$key]) ? http_build_query([$tmp[$key]]) : '')) {
						$current = FALSE;
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

		$this->lastCreatedRequest = new Application\Request(
			$presenter,
			Application\Request::FORWARD,
			$args,
			[],
			[]
		);
		$this->lastCreatedRequestFlag = ['current' => $current];

		if ($mode === 'forward' || $mode === 'test') {
			return NULL;
		}

		// CONSTRUCT URL
		if ($this->refUrlCache === NULL) {
			$this->refUrlCache = new Http\Url($this->httpRequest->getUrl());
			$this->refUrlCache->setPath($this->httpRequest->getUrl()->getScriptPath());
		}
		if (!$this->router) {
			throw new Nette\InvalidStateException('Unable to generate URL, service Router has not been set.');
		}
		$url = $this->router->constructUrl($this->lastCreatedRequest, $this->refUrlCache);
		if ($url === NULL) {
			unset($args[self::ACTION_KEY]);
			$params = urldecode(http_build_query($args, '', ', '));
			throw new InvalidLinkException("No route for $presenter:$action($params)");
		}

		// make URL relative if possible
		if ($mode === 'link' && $scheme === FALSE && !$this->absoluteUrls) {
			$hostUrl = $this->refUrlCache->getHostUrl() . '/';
			if (strncmp($url, $hostUrl, strlen($hostUrl)) === 0) {
				$url = substr($url, strlen($hostUrl) - 1);
			}
		}

		return $url . $fragment;
	}


	/**
	 * Converts list of arguments to named parameters.
	 * @param  string  class name
	 * @param  string  method name
	 * @param  array   arguments
	 * @param  array   supplemental arguments
	 * @param  ReflectionParameter[]  missing arguments
	 * @throws InvalidLinkException
	 * @internal
	 */
	public static function argsToParams(string $class, string $method, array &$args, array $supplemental = [], array &$missing = NULL): void
	{
		$i = 0;
		$rm = new \ReflectionMethod($class, $method);
		foreach ($rm->getParameters() as $param) {
			[$type, $isClass] = ComponentReflection::getParameterType($param);
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
				if (!$param->isDefaultValueAvailable() && !$param->allowsNull() && $type !== 'NULL' && $type !== 'array') {
					$missing[] = $param;
					unset($args[$name]);
				}
				continue;
			}

			if (!ComponentReflection::convertType($args[$name], $type, $isClass)) {
				throw new InvalidLinkException(sprintf(
					'Argument $%s passed to %s() must be %s, %s given.',
					$name,
					$rm->getDeclaringClass()->getName() . '::' . $rm->getName(),
					$type === 'NULL' ? 'scalar' : $type,
					is_object($args[$name]) ? get_class($args[$name]) : gettype($args[$name])
				));
			}

			$def = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : NULL;
			if ($args[$name] === $def || ($def === NULL && $args[$name] === '')) {
				$args[$name] = NULL; // value transmit is unnecessary
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

		$session[$key] = [$this->user ? $this->user->getId() : NULL, $this->request];
		$session->setExpiration($expiration, $key);
		return $key;
	}


	/**
	 * Restores request from session.
	 */
	public function restoreRequest(string $key): void
	{
		$session = $this->getSession('Nette.Application/requests');
		if (!isset($session[$key]) || ($session[$key][0] !== NULL && $session[$key][0] !== $this->getUser()->getId())) {
			return;
		}
		$request = clone $session[$key][1];
		unset($session[$key]);
		$request->setFlag(Application\Request::RESTORED, TRUE);
		$params = $request->getParameters();
		$params[self::FLASH_KEY] = $this->getFlashKey();
		$request->setParameters($params);
		$this->sendResponse(new Responses\ForwardResponse($request));
	}


	/********************* interface IStatePersistent ****************d*g**/


	/**
	 * Returns array of persistent components.
	 * This default implementation detects components by class-level annotation @persistent(cmp1, cmp2).
	 */
	public static function getPersistentComponents(): array
	{
		return (array) ComponentReflection::parseAnnotation(new \ReflectionClass(get_called_class()), 'persistent');
	}


	/**
	 * Saves state information for all subcomponents to $this->globalState.
	 */
	protected function getGlobalState($forClass = NULL): array
	{
		$sinces = &$this->globalStateSinces;

		if ($this->globalState === NULL) {
			$state = [];
			foreach ($this->globalParams as $id => $params) {
				$prefix = $id . self::NAME_SEPARATOR;
				foreach ($params as $key => $val) {
					$state[$prefix . $key] = $val;
				}
			}
			$this->saveState($state, $forClass ? new ComponentReflection($forClass) : NULL);

			if ($sinces === NULL) {
				$sinces = [];
				foreach ($this->getReflection()->getPersistentParams() as $name => $meta) {
					$sinces[$name] = $meta['since'];
				}
			}

			$components = $this->getReflection()->getPersistentComponents();
			$iterator = $this->getComponents(TRUE, IStatePersistent::class);

			foreach ($iterator as $name => $component) {
				if ($iterator->getDepth() === 0) {
					// counts with Nette\Application\RecursiveIteratorIterator::SELF_FIRST
					$since = $components[$name]['since'] ?? FALSE; // FALSE = nonpersistent
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

		if ($forClass !== NULL) {
			$since = NULL;
			foreach ($state as $key => $foo) {
				if (!isset($sinces[$key])) {
					$x = strpos($key, self::NAME_SEPARATOR);
					$x = $x === FALSE ? $key : substr($key, 0, $x);
					$sinces[$key] = $sinces[$x] ?? FALSE;
				}
				if ($since !== $sinces[$key]) {
					$since = $sinces[$key];
					$ok = $since && is_a($forClass, $since, TRUE);
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
		if (($tmp = $this->request->getPost('_' . self::SIGNAL_KEY)) !== NULL) {
			$params[self::SIGNAL_KEY] = $tmp;
		} elseif ($this->isAjax()) {
			$params += $this->request->getPost();
			if (($tmp = $this->request->getPost(self::SIGNAL_KEY)) !== NULL) {
				$params[self::SIGNAL_KEY] = $tmp;
			}
		}

		foreach ($params as $key => $value) {
			if (!preg_match('#^((?:[a-z0-9_]+-)*)((?!\d+\z)[a-z0-9_]+)\z#i', (string) $key, $matches)) {
				continue;
			} elseif (!$matches[1]) {
				$selfParams[$key] = $value;
			} else {
				$this->globalParams[substr($matches[1], 0, -1)][$matches[2]] = $value;
			}
		}

		// init & validate $this->action & $this->view
		$action = $selfParams[self::ACTION_KEY] ?? self::DEFAULT_ACTION;
		if (!is_string($action) || !Nette\Utils\Strings::match($action, '#^[a-zA-Z0-9][a-zA-Z0-9_\x7f-\xff]*\z#')) {
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
				$this->signal = substr($param, $pos + 1);
			} else {
				$this->signalReceiver = $this->getUniqueId();
				$this->signal = $param;
			}
			if ($this->signal == NULL) { // intentionally ==
				$this->signal = NULL;
			}
		}

		$this->loadState($selfParams);
	}


	/**
	 * Pops parameters for specified component.
	 * @param  string  component id
	 * @internal
	 */
	public function popGlobalParameters(string $id): array
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
			: NULL;
	}


	/**
	 * Checks if a flash session namespace exists.
	 */
	public function hasFlashSession(): bool
	{
		$flashKey = $this->getFlashKey();
		return $flashKey !== NULL
			&& $this->getSession()->hasSection('Nette.Application.Flash/' . $flashKey);
	}


	/**
	 * Returns session namespace provided to pass temporary data between redirects.
	 */
	public function getFlashSession(): Http\SessionSection
	{
		$flashKey = $this->getFlashKey();
		if ($flashKey === NULL) {
			$this->params[self::FLASH_KEY] = $flashKey = Nette\Utils\Random::generate(4);
		}
		return $this->getSession('Nette.Application.Flash/' . $flashKey);
	}


	/********************* services ****************d*g**/


	public function injectPrimary(Nette\DI\Container $context = NULL, Application\IPresenterFactory $presenterFactory = NULL, Application\IRouter $router = NULL,
		Http\IRequest $httpRequest, Http\IResponse $httpResponse, Http\Session $session = NULL, Nette\Security\User $user = NULL, ITemplateFactory $templateFactory = NULL)
	{
		if ($this->presenterFactory !== NULL) {
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
		return $this->context;
	}


	public function getHttpRequest(): Http\IRequest
	{
		return $this->httpRequest;
	}


	public function getHttpResponse(): Http\IResponse
	{
		return $this->httpResponse;
	}


	/**
	 * @return Http\Session|Http\SessionSection
	 */
	public function getSession(string $namespace = NULL)
	{
		if (!$this->session) {
			throw new Nette\InvalidStateException('Service Session has not been set.');
		}
		return $namespace === NULL ? $this->session : $this->session->getSection($namespace);
	}


	public function getUser(): Nette\Security\User
	{
		if (!$this->user) {
			throw new Nette\InvalidStateException('Service User has not been set.');
		}
		return $this->user;
	}


	public function getTemplateFactory(): ITemplateFactory
	{
		if (!$this->templateFactory) {
			throw new Nette\InvalidStateException('Service TemplateFactory has not been set.');
		}
		return $this->templateFactory;
	}

}
