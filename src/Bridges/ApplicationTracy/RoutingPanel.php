<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationTracy;

use Nette;
use Nette\Application\UI\Presenter;
use Nette\Routing;
use Tracy;


/**
 * Routing debugger for Debug Bar.
 */
final class RoutingPanel implements Tracy\IBarPanel
{
	private Routing\Router $router;
	private Nette\Http\IRequest $httpRequest;
	private Nette\Application\IPresenterFactory $presenterFactory;

	private array|\stdClass $routes;
	private ?array $matched = null;
	private \ReflectionClass|\ReflectionMethod|null $source = null;


	public function __construct(
		Routing\Router $router,
		Nette\Http\IRequest $httpRequest,
		Nette\Application\IPresenterFactory $presenterFactory,
	) {
		$this->router = $router;
		$this->httpRequest = $httpRequest;
		$this->presenterFactory = $presenterFactory;
	}


	/**
	 * Renders tab.
	 */
	public function getTab(): string
	{
		$this->routes = $this->analyse($this->router, $this->httpRequest);
		return Nette\Utils\Helpers::capture(function () {
			$matched = $this->matched;
			require __DIR__ . '/templates/RoutingPanel.tab.phtml';
		});
	}


	/**
	 * Renders panel.
	 */
	public function getPanel(): string
	{
		return Nette\Utils\Helpers::capture(function () {
			$matched = $this->matched;
			$routes = $this->routes;
			$source = $this->source;
			$url = $this->httpRequest->getUrl();
			$method = $this->httpRequest->getMethod();
			require __DIR__ . '/templates/RoutingPanel.panel.phtml';
		});
	}


	/**
	 * Analyses simple route.
	 */
	private function analyse(
		Routing\Router $router,
		?Nette\Http\IRequest $httpRequest,
		?\Closure $afterMatch = null,
		int $flag = 0,
	) {
		$afterMatch ??= fn($params) => $params;

		if ($router instanceof Routing\RouteList) {
			$info = [
				'path' => $router->getPath(),
				'domain' => $router->getDomain(),
				'module' => ($router instanceof Nette\Application\Routers\RouteList ? $router->getModule() : ''),
				'routes' => [],
			];

			$httpRequest = $httpRequest
				? (new \ReflectionMethod($router, 'beforeMatch'))->invoke($router, $httpRequest)
				: null;

			$afterMatch = function ($params) use ($router, $afterMatch) {
				$params = $params === null
					? null
					: (new \ReflectionMethod($router, 'afterMatch'))->invoke($router, $params);
				return $afterMatch($params);
			};

			$flags = $router->getFlags();
			foreach ($router->getRouters() as $i => $innerRouter) {
				$info['routes'][] = $this->analyse($innerRouter, $httpRequest, $afterMatch, $flags[$i]);
			}

			return $info;
		}

		$matched = $flag & Routing\RouteList::ONE_WAY ? 'oneway' : 'no';
		$params = $e = null;
		try {
			$params = $httpRequest ? $afterMatch($router->match($httpRequest)) : null;
		} catch (\Throwable $e) {
			$matched = 'error';
		}

		if ($params !== null) {
			$matched = 'may';
			if ($this->matched === null) {
				$this->matched = $params;
				$this->findSource();
				$matched = 'yes';
			}
		}

		return (object) [
			'matched' => $matched,
			'class' => $router::class,
			'defaults' => $router instanceof Routing\Route || $router instanceof Routing\SimpleRouter ? $router->getDefaults() : [],
			'mask' => $router instanceof Routing\Route ? $router->getMask() : null,
			'params' => $params,
			'error' => $e,
		];
	}


	private function findSource(): void
	{
		$params = $this->matched;
		$presenter = $params['presenter'] ?? '';
		try {
			$class = $this->presenterFactory->getPresenterClass($presenter);
		} catch (Nette\Application\InvalidPresenterException $e) {
			return;
		}

		$rc = new \ReflectionClass($class);

		if ($rc->isSubclassOf(Nette\Application\UI\Presenter::class)) {
			if (isset($params[Presenter::SignalKey])) {
				$method = $class::formatSignalMethod($params[Presenter::SignalKey]);

			} elseif (isset($params[Presenter::ActionKey])) {
				$action = $params[Presenter::ActionKey];
				$method = $class::formatActionMethod($action);
				if (!$rc->hasMethod($method)) {
					$method = $class::formatRenderMethod($action);
				}
			}
		}

		$this->source = isset($method) && $rc->hasMethod($method)
			? $rc->getMethod($method)
			: $rc;
	}
}
