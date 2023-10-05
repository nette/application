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

	/** @var Routing\Router */
	private $router;

	/** @var Nette\Http\IRequest */
	private $httpRequest;

	/** @var Nette\Application\IPresenterFactory */
	private $presenterFactory;

	/** @var \stdClass[] */
	private $routers = [];

	/** @var array|null */
	private $matched;

	/** @var \ReflectionClass|\ReflectionMethod */
	private $source;


	public function __construct(
		Routing\Router $router,
		Nette\Http\IRequest $httpRequest,
		Nette\Application\IPresenterFactory $presenterFactory
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
		$this->analyse($this->router, $this->httpRequest);
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
			$routers = $this->routers;
			$source = $this->source;
			$hasModule = (bool) array_filter($routers, function (\stdClass $rq): string { return $rq->module; });
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
		string $module = '',
		string $path = '',
		int $level = -1,
		int $flag = 0
	): void
	{
		if ($router instanceof Routing\RouteList) {
			if ($httpRequest) {
				try {
					$httpRequest = $router->match($httpRequest) === null ? null : $httpRequest;
				} catch (\Throwable $e) {
					$httpRequest = null;
				}
			}

			$prop = (new \ReflectionProperty(Routing\RouteList::class, 'path'));
			$prop->setAccessible(true);
			$path .= $pathPrefix = $prop->getValue($router);
			if ($httpRequest && $pathPrefix) {
				$url = $httpRequest->getUrl();
				$url = $url->getRelativePath() . '/' === $pathPrefix
					? $url->withPath($url->getPath() . '/')
					: $url->withPath($url->getPath(), $url->getBasePath() . $pathPrefix);
				$httpRequest = $httpRequest->withUrl($url);
			}

			$module .= ($router instanceof Nette\Application\Routers\RouteList ? $router->getModule() : '');

			$next = count($this->routers);
			$flags = $router->getFlags();
			foreach ($router->getRouters() as $i => $subRouter) {
				$this->analyse($subRouter, $httpRequest, $module, $path, $level + 1, $flags[$i]);
			}

			if ($info = $this->routers[$next] ?? null) {
				$info->gutterTop = abs(max(0, $level) - $info->level);
			}

			if ($info = end($this->routers)) {
				$info->gutterBottom = abs(max(0, $level) - $info->level);
			}

			return;
		}

		$matched = $flag & Routing\RouteList::ONE_WAY ? 'oneway' : 'no';
		$params = $e = null;
		try {
			$params = $httpRequest
				? $router->match($httpRequest)
				: null;
		} catch (\Throwable $e) {
			$matched = 'error';
		}

		if ($params !== null) {
			if ($module) {
				$params['presenter'] = $module . ($params['presenter'] ?? '');
			}

			$matched = 'may';
			if ($this->matched === null) {
				$this->matched = $params;
				$this->findSource();
				$matched = 'yes';
			}
		}

		$this->routers[] = (object) [
			'level' => max(0, $level),
			'matched' => $matched,
			'class' => get_class($router),
			'defaults' => $router instanceof Routing\Route || $router instanceof Routing\SimpleRouter ? $router->getDefaults() : [],
			'mask' => $router instanceof Routing\Route ? $router->getMask() : null,
			'params' => $params,
			'module' => rtrim($module, ':'),
			'path' => $path,
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
