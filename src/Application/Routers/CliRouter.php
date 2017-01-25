<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\Routers;

use Nette;
use Nette\Application;


/**
 * The unidirectional router for CLI. (experimental)
 */
class CliRouter implements Application\IRouter
{
	use Nette\SmartObject;

	const PRESENTER_KEY = 'action';

	/** @var array */
	private $defaults;


	/**
	 * @param  array   default values
	 */
	public function __construct(array $defaults = [])
	{
		$this->defaults = $defaults;
	}


	/**
	 * Maps command line arguments to a Request object.
	 */
	public function match(Nette\Http\IRequest $httpRequest): ?Application\Request
	{
		if (empty($_SERVER['argv']) || !is_array($_SERVER['argv'])) {
			return NULL;
		}

		$names = [self::PRESENTER_KEY];
		$params = $this->defaults;
		$args = $_SERVER['argv'];
		array_shift($args);
		$args[] = '--';

		foreach ($args as $arg) {
			$opt = preg_replace('#/|-+#A', '', $arg);
			if ($opt === $arg) {
				if (isset($flag) || $flag = array_shift($names)) {
					$params[$flag] = $arg;
				} else {
					$params[] = $arg;
				}
				$flag = NULL;
				continue;
			}

			if (isset($flag)) {
				$params[$flag] = TRUE;
				$flag = NULL;
			}

			if ($opt !== '') {
				$pair = explode('=', $opt, 2);
				if (isset($pair[1])) {
					$params[$pair[0]] = $pair[1];
				} else {
					$flag = $pair[0];
				}
			}
		}

		if (!isset($params[self::PRESENTER_KEY])) {
			throw new Nette\InvalidStateException('Missing presenter & action in route definition.');
		}
		[$module, $presenter] = Nette\Application\Helpers::splitName($params[self::PRESENTER_KEY]);
		if ($module !== '') {
			$params[self::PRESENTER_KEY] = $presenter;
			$presenter = $module;
		}

		return new Application\Request(
			$presenter,
			'CLI',
			$params
		);
	}


	/**
	 * This router is only unidirectional.
	 */
	public function constructUrl(Application\Request $appRequest, Nette\Http\Url $refUrl): ?string
	{
		return NULL;
	}


	/**
	 * Returns default values.
	 */
	public function getDefaults(): array
	{
		return $this->defaults;
	}

}
