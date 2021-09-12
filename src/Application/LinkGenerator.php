<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application;

use Nette;
use Nette\Http\UrlScript;
use Nette\Routing\Router;


/**
 * Link generator.
 */
final class LinkGenerator
{
	use Nette\SmartObject;

	public function __construct(
		private Router $router,
		private UrlScript $refUrl,
		private ?IPresenterFactory $presenterFactory = null,
	) {
	}


	/**
	 * Generates URL to presenter.
	 * @param  string   $dest in format "[[[module:]presenter:]action] [#fragment]"
	 * @throws UI\InvalidLinkException
	 */
	public function link(string $dest, array $params = []): string
	{
		if (!preg_match('~^([\w:]+):(\w*+)(#.*)?()$~D', $dest, $m)) {
			throw new UI\InvalidLinkException("Invalid link destination '$dest'.");
		}
		[, $presenter, $action, $frag] = $m;

		try {
			$class = $this->presenterFactory
				? $this->presenterFactory->getPresenterClass($presenter)
				: null;
		} catch (InvalidPresenterException $e) {
			throw new UI\InvalidLinkException($e->getMessage(), 0, $e);
		}

		if (is_subclass_of($class, UI\Presenter::class)) {
			if ($action === '') {
				$action = UI\Presenter::DEFAULT_ACTION;
			}
			if (
				method_exists($class, $method = $class::formatActionMethod($action))
				|| method_exists($class, $method = $class::formatRenderMethod($action))
			) {
				UI\Presenter::argsToParams($class, $method, $params, [], $missing);
				if ($missing) {
					$rp = $missing[0];
					throw new UI\InvalidLinkException("Missing parameter \${$rp->getName()} required by {$rp->getDeclaringClass()->getName()}::{$rp->getDeclaringFunction()->getName()}()");
				}

			} elseif (array_key_exists(0, $params)) {
				throw new UI\InvalidLinkException("Unable to pass parameters to action '$presenter:$action', missing corresponding method.");
			}
		}

		if ($action !== '') {
			$params[UI\Presenter::ACTION_KEY] = $action;
		}
		$params[UI\Presenter::PRESENTER_KEY] = $presenter;

		$url = $this->router->constructUrl($params, $this->refUrl);
		if ($url === null) {
			unset($params[UI\Presenter::ACTION_KEY], $params[UI\Presenter::PRESENTER_KEY]);
			$paramsDecoded = urldecode(http_build_query($params, '', ', '));
			throw new UI\InvalidLinkException("No route for $dest($paramsDecoded)");
		}
		return $url . $frag;
	}


	public function withReferenceUrl(string $url): static
	{
		return new self(
			$this->router,
			new UrlScript($url),
			$this->presenterFactory,
		);
	}
}
