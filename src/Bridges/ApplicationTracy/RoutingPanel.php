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
use Tracy\Dumper;


/**
 * Routing debugger for Debug Bar.
 */
final class RoutingPanel implements Tracy\IBarPanel
{
	use Nette\SmartObject;

	/** @var Nette\Application\IRouter */
	private $router;

	/** @var Nette\Http\IRequest */
	private $httpRequest;

	/** @var Nette\Application\IPresenterFactory */
	private $presenterFactory;

	/** @var array */
	private $routers = [];

	/** @var Nette\Application\Request */
	private $request;

	/** @var \ReflectionClass|\ReflectionMethod */
	private $source;


	public static function initializePanel(Nette\Application\Application $application): void
	{
		Tracy\Debugger::getBlueScreen()->addPanel(function ($e) use ($application) {
			return $e ? null : [
				'tab' => 'Nette Application',
				'panel' => '<h3>Requests</h3>' . Dumper::toHtml($application->getRequests(), [Dumper::LIVE => true])
					. '<h3>Presenter</h3>' . Dumper::toHtml($application->getPresenter(), [Dumper::LIVE => true]),
			];
		});
	}


	public function __construct(Nette\Application\IRouter $router, Nette\Http\IRequest $httpRequest, Nette\Application\IPresenterFactory $presenterFactory)
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
		$request = $this->request;
		require __DIR__ . '/templates/RoutingPanel.tab.phtml';
		return ob_get_clean();
	}


	/**
	 * Renders panel.
	 */
	public function getPanel(): string
	{
		ob_start(function () {});
		$request = $this->request;
		$routers = $this->routers;
		$source = $this->source;
		$hasModule = (bool) array_filter($routers, function ($rq) { return $rq['module']; });
		$url = $this->httpRequest->getUrl();
		$method = $this->httpRequest->getMethod();
		require __DIR__ . '/templates/RoutingPanel.panel.phtml';
		return ob_get_clean();
	}


	/**
	 * Analyses simple route.
	 */
	private function analyse(Nette\Application\IRouter $router, string $module = ''): void
	{
		if ($router instanceof Routers\RouteList) {
			foreach ($router as $subRouter) {
				$this->analyse($subRouter, $module . $router->getModule());
			}
			return;
		}

		$matched = 'no';
		$request = $router->match($this->httpRequest);
		if ($request) {
			$request->setPresenterName($module . $request->getPresenterName());
			$matched = 'may';
			if (empty($this->request)) {
				$this->request = $request;
				$this->findSource();
				$matched = 'yes';
			}
		}

		$this->routers[] = [
			'matched' => $matched,
			'class' => get_class($router),
			'defaults' => $router instanceof Routers\Route || $router instanceof Routers\SimpleRouter ? $router->getDefaults() : [],
			'mask' => $router instanceof Routers\Route ? $router->getMask() : null,
			'request' => $request,
			'module' => rtrim($module, ':'),
		];
	}


	private function findSource(): void
	{
		$request = $this->request;
		$presenter = $request->getPresenterName();
		try {
			$class = $this->presenterFactory->getPresenterClass($presenter);
		} catch (Nette\Application\InvalidPresenterException $e) {
			return;
		}
		$rc = new \ReflectionClass($class);

		if ($rc->isSubclassOf(Nette\Application\UI\Presenter::class)) {
			if ($request->getParameter(Presenter::SIGNAL_KEY)) {
				$method = $class::formatSignalMethod($request->getParameter(Presenter::SIGNAL_KEY));

			} elseif ($request->getParameter(Presenter::ACTION_KEY)) {
				$action = $request->getParameter(Presenter::ACTION_KEY);
				$method = $class::formatActionMethod($action);
				if (!$rc->hasMethod($method)) {
					$method = $class::formatRenderMethod($action);
				}
			}
		}

		$this->source = isset($method) && $rc->hasMethod($method) ? $rc->getMethod($method) : $rc;
	}
}
