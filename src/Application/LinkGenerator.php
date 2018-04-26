<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application;

use Nette;


/**
 * Link generator.
 */
class LinkGenerator
{
	use Nette\SmartObject;

	/** @var IRouter */
	private $router;

	/** @var Nette\Http\Url */
	private $refUrl;

	/** @var IPresenterFactory|null */
	private $presenterFactory;


	public function __construct(IRouter $router, Nette\Http\Url $refUrl, IPresenterFactory $presenterFactory = null)
	{
		$this->router = $router;
		$this->refUrl = $refUrl;
		$this->presenterFactory = $presenterFactory;
	}


	/**
	 * Generates URL to presenter.
	 * @param  string   destination in format "[[[module:]presenter:]action] [#fragment]"
	 * @return string
	 * @throws UI\InvalidLinkException
	 */
	public function link($dest, array $params = [])
	{
		if (!preg_match('~^([\w:]+):(\w*+)(#.*)?()\z~', $dest, $m)) {
			throw new UI\InvalidLinkException("Invalid link destination '$dest'.");
		}
		list(, $presenter, $action, $frag) = $m;

		try {
			$class = $this->presenterFactory ? $this->presenterFactory->getPresenterClass($presenter) : null;
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

		$url = $this->router->constructUrl(new Request($presenter, null, $params), $this->refUrl);
		if ($url === null) {
			unset($params[UI\Presenter::ACTION_KEY]);
			$params = urldecode(http_build_query($params, null, ', '));
			throw new UI\InvalidLinkException("No route for $dest($params)");
		}
		return $url . $frag;
	}
}
