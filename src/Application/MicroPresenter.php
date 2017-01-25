<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace NetteModule;

use Nette;
use Nette\Application;
use Nette\Application\Responses;
use Nette\Http;
use Latte;


/**
 * Micro presenter.
 */
class MicroPresenter implements Application\IPresenter
{
	use Nette\SmartObject;

	/** @var Nette\DI\Container|NULL */
	private $context;

	/** @var Nette\Http\IRequest|NULL */
	private $httpRequest;

	/** @var Application\IRouter|NULL */
	private $router;

	/** @var Application\Request */
	private $request;


	public function __construct(Nette\DI\Container $context = NULL, Http\IRequest $httpRequest = NULL, Application\IRouter $router = NULL)
	{
		$this->context = $context;
		$this->httpRequest = $httpRequest;
		$this->router = $router;
	}


	/**
	 * Gets the context.
	 */
	public function getContext(): Nette\DI\Container
	{
		return $this->context;
	}


	public function run(Application\Request $request): Application\IResponse
	{
		$this->request = $request;

		if ($this->httpRequest && $this->router && !$this->httpRequest->isAjax() && ($request->isMethod('get') || $request->isMethod('head'))) {
			$refUrl = clone $this->httpRequest->getUrl();
			$url = $this->router->constructUrl($request, $refUrl->setPath($refUrl->getScriptPath()));
			if ($url !== NULL && !$this->httpRequest->getUrl()->isEqual($url)) {
				return new Responses\RedirectResponse($url, Http\IResponse::S301_MOVED_PERMANENTLY);
			}
		}

		$params = $request->getParameters();
		if (!isset($params['callback'])) {
			throw new Application\BadRequestException('Parameter callback is missing.');
		}
		$callback = $params['callback'];
		$reflection = Nette\Utils\Callback::toReflection(Nette\Utils\Callback::check($callback));

		if ($this->context) {
			foreach ($reflection->getParameters() as $param) {
				if ($param->getClass()) {
					$params[$param->getName()] = $this->context->getByType($param->getClass()->getName(), FALSE);
				}
			}
		}
		$params['presenter'] = $this;
		$params = Application\UI\ComponentReflection::combineArgs($reflection, $params);

		$response = $callback(...array_values($params));

		if (is_string($response)) {
			$response = [$response, []];
		}
		if (is_array($response)) {
			[$templateSource, $templateParams] = $response;
			$response = $this->createTemplate()->setParameters($templateParams);
			if (!$templateSource instanceof \SplFileInfo) {
				$response->getLatte()->setLoader(new Latte\Loaders\StringLoader);
			}
			$response->setFile((string) $templateSource);
		}
		if ($response instanceof Application\UI\ITemplate) {
			return new Responses\TextResponse($response);
		} else {
			return $response ?: new Responses\VoidResponse;
		}
	}


	/**
	 * Template factory.
	 */
	public function createTemplate(string $class = NULL, callable $latteFactory = NULL): Application\UI\ITemplate
	{
		$latte = $latteFactory ? $latteFactory() : $this->getContext()->getByType(Nette\Bridges\ApplicationLatte\ILatteFactory::class)->create();
		$template = $class ? new $class : new Nette\Bridges\ApplicationLatte\Template($latte);

		$template->setParameters($this->request->getParameters());
		$template->presenter = $this;
		$template->context = $this->context;
		if ($this->httpRequest) {
			$url = $this->httpRequest->getUrl();
			$template->baseUrl = rtrim($url->getBaseUrl(), '/');
			$template->basePath = rtrim($url->getBasePath(), '/');
		}
		return $template;
	}


	/**
	 * Redirects to another URL.
	 * @param  int $code HTTP code
	 */
	public function redirectUrl(string $url, int $code = Http\IResponse::S302_FOUND): Responses\RedirectResponse
	{
		return new Responses\RedirectResponse($url, $code);
	}


	/**
	 * Throws HTTP error.
	 * @param  int $code HTTP error code
	 * @throws Nette\Application\BadRequestException
	 */
	public function error(string $message = NULL, int $code = Http\IResponse::S404_NOT_FOUND): void
	{
		throw new Application\BadRequestException($message, $code);
	}


	public function getRequest(): Nette\Application\Request
	{
		return $this->request;
	}

}
