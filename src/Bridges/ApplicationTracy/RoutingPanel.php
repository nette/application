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
	use Nette\SmartObject;

	private array $routes;
	private ?array $matched = null;


	public function __construct(
		private readonly Routing\Router $router,
		private readonly Nette\Http\IRequest $httpRequest,
		private readonly Nette\Application\IPresenterFactory $presenterFactory,
	) {
	}


	/**
	 * Renders tab.
	 */
	public function getTab(): string
	{
		$this->routes = $this->analyse(
			$this->router instanceof Routing\RouteList
				? $this->router
				: (new Routing\RouteList)->add($this->router),
			$this->httpRequest,
		);
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
			$source = $this->matched ? $this->findSource() : null;
			$url = $this->httpRequest->getUrl();
			$method = $this->httpRequest->getMethod();
			require __DIR__ . '/templates/RoutingPanel.panel.phtml';
		});
	}


	private function analyse(Routing\RouteList $router, ?Nette\Http\IRequest $httpRequest): array
	{
		$res = [
			'path' => $router->getPath(),
			'domain' => $router->getDomain(),
			'module' => ($router instanceof Nette\Application\Routers\RouteList ? $router->getModule() : ''),
			'routes' => [],
		];
		$httpRequest = $httpRequest
			? (fn() => $this->prepareRequest($httpRequest))->bindTo($router, Routing\RouteList::class)()
			: null;
		$flags = $router->getFlags();

		foreach ($router->getRouters() as $i => $innerRouter) {
			if ($innerRouter instanceof Routing\RouteList) {
				$res['routes'][] = $this->analyse($innerRouter, $httpRequest);
				continue;
			}

			$matched = $flags[$i] & $router::ONE_WAY ? 'oneway' : 'no';
			$params = $e = null;
			try {
				if (
					$httpRequest
					&& ($params = $innerRouter->match($httpRequest)) !== null
					&& ($params = (fn() => $this->completeParameters($params))->bindTo($router, Routing\RouteList::class)()) !== null
				) {
					$matched = 'may';
					if ($this->matched === null) {
						$this->matched = $params;
						$matched = 'yes';
					}
				}
			} catch (\Throwable $e) {
				$matched = 'error';
			}

			$res['routes'][] = (object) [
				'matched' => $matched,
				'class' => $innerRouter::class,
				'defaults' => $innerRouter instanceof Routing\Route || $innerRouter instanceof Routing\SimpleRouter ? $innerRouter->getDefaults() : [],
				'mask' => $innerRouter instanceof Routing\Route ? $innerRouter->getMask() : null,
				'params' => $params,
				'error' => $e,
			];
		}
		return $res;
	}


	private function findSource(): \ReflectionClass|\ReflectionMethod|null
	{
		$params = $this->matched;
		$presenter = $params['presenter'] ?? '';
		try {
			$class = $this->presenterFactory->getPresenterClass($presenter);
		} catch (Nette\Application\InvalidPresenterException) {
			return null;
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

		return isset($method) && $rc->hasMethod($method)
			? $rc->getMethod($method)
			: $rc;
	}
}
