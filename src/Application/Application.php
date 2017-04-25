<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application;

use Nette;


/**
 * Front Controller.
 */
class Application
{
	use Nette\SmartObject;

	/** @var int */
	public static $maxLoop = 20;

	/** @var bool enable fault barrier? */
	public $catchExceptions;

	/** @var string */
	public $errorPresenter;

	/** @var callable[]  function (Application $sender); Occurs before the application loads presenter */
	public $onStartup;

	/** @var callable[]  function (Application $sender, \Throwable $e = NULL); Occurs before the application shuts down */
	public $onShutdown;

	/** @var callable[]  function (Application $sender, Request $request); Occurs when a new request is received */
	public $onRequest;

	/** @var callable[]  function (Application $sender, IPresenter $presenter); Occurs when a presenter is created */
	public $onPresenter;

	/** @var callable[]  function (Application $sender, IResponse $response); Occurs when a new response is ready for dispatch */
	public $onResponse;

	/** @var callable[]  function (Application $sender, \Throwable $e); Occurs when an unhandled exception occurs in the application */
	public $onError;

	/** @var Request[] */
	private $requests = [];

	/** @var IPresenter|NULL */
	private $presenter;

	/** @var Nette\Http\IRequest */
	private $httpRequest;

	/** @var Nette\Http\IResponse */
	private $httpResponse;

	/** @var IPresenterFactory */
	private $presenterFactory;

	/** @var IRouter */
	private $router;


	public function __construct(IPresenterFactory $presenterFactory, IRouter $router, Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse)
	{
		$this->httpRequest = $httpRequest;
		$this->httpResponse = $httpResponse;
		$this->presenterFactory = $presenterFactory;
		$this->router = $router;
	}


	/**
	 * Dispatch a HTTP request to a front controller.
	 */
	public function run(): void
	{
		try {
			$this->onStartup($this);
			$this->processRequest($this->createInitialRequest());
			$this->onShutdown($this);

		} catch (\Throwable $e) {
			$this->onError($this, $e);
			if ($this->catchExceptions && $this->errorPresenter) {
				try {
					$this->processException($e);
					$this->onShutdown($this, $e);
					return;

				} catch (\Throwable $e) {
					$this->onError($this, $e);
				}
			}
			$this->onShutdown($this, $e);
			throw $e;
		}
	}


	public function createInitialRequest(): Request
	{
		$request = $this->router->match($this->httpRequest);
		if (!$request) {
			throw new BadRequestException('No route for HTTP request.');
		}
		return $request;
	}


	public function processRequest(Request $request): void
	{
		process:
		if (count($this->requests) > self::$maxLoop) {
			throw new ApplicationException('Too many loops detected in application life cycle.');
		}

		$this->requests[] = $request;
		$this->onRequest($this, $request);

		if (!$request->isMethod($request::FORWARD) && !strcasecmp($request->getPresenterName(), (string) $this->errorPresenter)) {
			throw new BadRequestException('Invalid request. Presenter is not achievable.');
		}

		try {
			$this->presenter = $this->presenterFactory->createPresenter($request->getPresenterName());
		} catch (InvalidPresenterException $e) {
			throw count($this->requests) > 1 ? $e : new BadRequestException($e->getMessage(), 0, $e);
		}
		$this->onPresenter($this, $this->presenter);
		$response = $this->presenter->run(clone $request);

		if ($response instanceof Responses\ForwardResponse) {
			$request = $response->getRequest();
			goto process;
		}

		$this->onResponse($this, $response);
		$response->send($this->httpRequest, $this->httpResponse);
	}


	public function processException(\Throwable $e): void
	{
		if (!$e instanceof BadRequestException && $this->httpResponse instanceof Nette\Http\Response) {
			$this->httpResponse->warnOnBuffer = FALSE;
		}
		if (!$this->httpResponse->isSent()) {
			$this->httpResponse->setCode($e instanceof BadRequestException ? ($e->getHttpCode() ?: 404) : 500);
		}

		$args = ['exception' => $e, 'request' => end($this->requests) ?: NULL];
		if ($this->presenter instanceof UI\Presenter) {
			try {
				$this->presenter->forward(":$this->errorPresenter:", $args);
			} catch (AbortException $foo) {
				$this->processRequest($this->presenter->getLastCreatedRequest());
			}
		} else {
			$this->processRequest(new Request($this->errorPresenter, Request::FORWARD, $args));
		}
	}


	/**
	 * Returns all processed requests.
	 * @return Request[]
	 */
	public function getRequests(): array
	{
		return $this->requests;
	}


	/**
	 * Returns current presenter.
	 */
	public function getPresenter(): ?IPresenter
	{
		return $this->presenter;
	}


	/********************* services ****************d*g**/


	/**
	 * Returns router.
	 */
	public function getRouter(): IRouter
	{
		return $this->router;
	}


	/**
	 * Returns presenter factory.
	 */
	public function getPresenterFactory(): IPresenterFactory
	{
		return $this->presenterFactory;
	}

}
