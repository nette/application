<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\Routers;

use Nette;


/**
 * The unidirectional router for CLI. (experimental)
 */
final class CliRouter implements Nette\Routing\Router
{
	use Nette\SmartObject;

	private const PRESENTER_KEY = 'action';

	/** @var array */
	private $defaults;


	public function __construct(array $defaults = [])
	{
		$this->defaults = $defaults;
	}


	/**
	 * Maps command line arguments to an array.
	 */
	public function match(Nette\Http\IRequest $httpRequest): ?array
	{
		if (empty($_SERVER['argv']) || !is_array($_SERVER['argv'])) {
			return null;
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

				$flag = null;
				continue;
			}

			if (isset($flag)) {
				$params[$flag] = true;
				$flag = null;
			}

			if ($opt === '') {
				continue;
			}

			$pair = explode('=', $opt, 2);
			if (isset($pair[1])) {
				$params[$pair[0]] = $pair[1];
			} else {
				$flag = $pair[0];
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

		$params['presenter'] = $presenter;

		return $params;
	}


	/**
	 * This router is only unidirectional.
	 */
	public function constructUrl(array $params, Nette\Http\UrlScript $refUrl): ?string
	{
		return null;
	}


	/**
	 * Returns default values.
	 */
	public function getDefaults(): array
	{
		return $this->defaults;
	}
}
