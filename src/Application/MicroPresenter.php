<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace NetteModule;

use Latte;
use Nette;
use Nette\Application;
use Nette\Application\Responses;
use Nette\Http;
use Nette\Routing\Router;


/**
 * Micro presenter.
 */
final class MicroPresenter implements Application\IPresenter
{
	use Nette\SmartObject;

	/** @var Nette\DI\Container|null */
	private $context;

	/** @var Nette\Http\IRequest|null */
	private $httpRequest;

	/** @var Router|null */
	private $router;

	/** @var Application\Request|null */
	private $request;


	public function __construct(
		Nette\DI\Container $context = null,
		Http\IRequest $httpRequest = null,
		Router $router = null
	) {
		$this->context = $context;
		$this->httpRequest = $httpRequest;
		$this->router = $router;
	}


	/**
	 * Gets the context.
	 */
	public function getContext(): ?Nette\DI\Container
	{
		return $this->context;
	}


	public function run(Application\Request $request): Application\IResponse
	{
		$this->request = $request;

		if (
			$this->httpRequest
			&& $this->router
			&& !$this->httpRequest->isAjax()
			&& ($request->isMethod('get') || $request->isMethod('head'))
		) {
			$refUrl = $this->httpRequest->getUrl()->withoutUserInfo();
			$url = $this->router->constructUrl($request->toArray(), $refUrl);
			if ($url !== null && !$refUrl->isEqual($url)) {
				return new Responses\RedirectResponse($url, Http\IResponse::S301_MOVED_PERMANENTLY);
			}
		}

		$params = $request->getParameters();
		$callback = $params['callback'] ?? null;
		if (!is_object($callback) || !is_callable($callback)) {
			throw new Application\BadRequestException('Parameter callback is not a valid closure.');
		}
		$reflection = Nette\Utils\Callback::toReflection($callback);

		if ($this->context) {
			foreach ($reflection->getParameters() as $param) {
				if ($param->getType()) {
					$params[$param->getName()] = $this->context->getByType($param->getType()->getName(), false);
				}
			}
		}
		$params['presenter'] = $this;
		try {
			$params = Application\UI\ComponentReflection::combineArgs($reflection, $params);
		} catch (Nette\InvalidArgumentException $e) {
			$this->error($e->getMessage());
		}

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
	public function createTemplate(string $class = null, callable $latteFactory = null): Application\UI\ITemplate
	{
		$latte = $latteFactory
			? $latteFactory()
			: $this->getContext()->getByType(Nette\Bridges\ApplicationLatte\ILatteFactory::class)->create();
		$template = $class
			? new $class
			: new Nette\Bridges\ApplicationLatte\DefaultTemplate($latte);

		$template->setParameters($this->request->getParameters());
		$template->presenter = $this;
		$template->context = $this->context;
		if ($this->httpRequest) {
			$url = $this->httpRequest->getUrl()->withoutUserInfo();
			$template->baseUrl = rtrim($url->getBaseUrl(), '/');
			$template->basePath = rtrim($url->getBasePath(), '/');
		}
		return $template;
	}


	/**
	 * Redirects to another URL.
	 */
	public function redirectUrl(string $url, int $httpCode = Http\IResponse::S302_FOUND): Responses\RedirectResponse
	{
		return new Responses\RedirectResponse($url, $httpCode);
	}


	/**
	 * Throws HTTP error.
	 * @throws Nette\Application\BadRequestException
	 */
	public function error(string $message = '', int $httpCode = Http\IResponse::S404_NOT_FOUND): void
	{
		throw new Application\BadRequestException($message, $httpCode);
	}


	public function getRequest(): ?Nette\Application\Request
	{
		return $this->request;
	}
}
