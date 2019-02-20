<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationTracy;

use Nette;
use Nette\Application\Routers;
use Nette\Application\UI\Presenter;
use Tracy;


/**
 * Routing debugger for Debug Bar.
 */
final class RoutingPanel implements Tracy\IBarPanel
{
	use Nette\SmartObject;

	/** @var Nette\Routing\Router */
	private $router;

	/** @var Nette\Http\IRequest */
	private $httpRequest;

	/** @var Nette\Application\IPresenterFactory */
	private $presenterFactory;

	/** @var array */
	private $routers = [];

	/** @var array|null */
	private $matched;

	/** @var \ReflectionClass|\ReflectionMethod */
	private $source;


	public static function initializePanel(Nette\Application\Application $application): void
	{
		$blueScreen = Tracy\Debugger::getBlueScreen();
		$blueScreen->addPanel(function (?\Throwable $e) use ($application, $blueScreen): ?array {
			$dumper = $blueScreen->getDumper();
			return $e ? null : [
				'tab' => 'Nette Application',
				'panel' => '<h3>Requests</h3>' . $dumper($application->getRequests())
					. '<h3>Presenter</h3>' . $dumper($application->getPresenter()),
			];
		});
	}


	public function __construct(Nette\Routing\Router $router, Nette\Http\IRequest $httpRequest, Nette\Application\IPresenterFactory $presenterFactory)
	{
		$this->router = $router;
		$this->httpRequest = $httpRequest;
		$this->presenterFactory = $presenterFactory;
	}


	/**
	 * Renders tab.
	 */
	public function getTab(): string
	{
		$this->analyse($this->router);
		ob_start(function () {});
		$matched = $this->matched;
		require __DIR__ . '/templates/RoutingPanel.tab.phtml';
		return ob_get_clean();
	}


	/**
	 * Renders panel.
	 */
	public function getPanel(): string
	{
		ob_start(function () {});
		$matched = $this->matched;
		$routers = $this->routers;
		$source = $this->source;
		$hasModule = (bool) array_filter($routers, function (array $rq): string { return $rq['module']; });
		$url = $this->httpRequest->getUrl();
		$method = $this->httpRequest->getMethod();
		require __DIR__ . '/templates/RoutingPanel.panel.phtml';
		return ob_get_clean();
	}


	/**
	 * Analyses simple route.
	 */
	private function analyse(Nette\Routing\Router $router, string $module = ''): void
	{
		if ($router instanceof Routers\RouteList) {
			foreach ($router as $subRouter) {
				$this->analyse($subRouter, $module . $router->getModule());
			}
			return;
		}

		$matched = 'no';
		$params = $e = null;
		try {
			$params = $router->match($this->httpRequest);
		} catch (\Exception $e) {
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

		$this->routers[] = [
			'matched' => $matched,
			'class' => get_class($router),
			'defaults' => $router instanceof Routers\Route || $router instanceof Routers\SimpleRouter ? $router->getDefaults() : [],
			'mask' => $router instanceof Routers\Route ? $router->getMask() : null,
			'params' => $params,
			'module' => rtrim($module, ':'),
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
			if (isset($params[Presenter::SIGNAL_KEY])) {
				$method = $class::formatSignalMethod($params[Presenter::SIGNAL_KEY]);

			} elseif (isset($params[Presenter::ACTION_KEY])) {
				$action = $params[Presenter::ACTION_KEY];
				$method = $class::formatActionMethod($action);
				if (!$rc->hasMethod($method)) {
					$method = $class::formatRenderMethod($action);
				}
			}
		}

		$this->source = isset($method) && $rc->hasMethod($method) ? $rc->getMethod($method) : $rc;
	}
}
