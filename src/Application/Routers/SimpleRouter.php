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
 * The bidirectional route for trivial routing via query parameters.
 */
final class SimpleRouter implements Application\IRouter
{
	use Nette\SmartObject;

	private const
		PRESENTER_KEY = 'presenter',
		MODULE_KEY = 'module';

	/** @var string */
	private $module = '';

	/** @var array */
	private $defaults;

	/** @var int */
	private $flags;


	public function __construct($defaults = [], int $flags = 0)
	{
		if (is_string($defaults)) {
			[$presenter, $action] = Nette\Application\Helpers::splitName($defaults);
			if (!$presenter) {
				throw new Nette\InvalidArgumentException("Argument must be array or string in format Presenter:action, '$defaults' given.");
			}
			$defaults = [
				self::PRESENTER_KEY => $presenter,
				'action' => $action === '' ? Application\UI\Presenter::DEFAULT_ACTION : $action,
			];
		}

		if (isset($defaults[self::MODULE_KEY])) {
			$this->module = $defaults[self::MODULE_KEY] . ':';
			unset($defaults[self::MODULE_KEY]);
		}

		$this->defaults = $defaults;
		$this->flags = $flags;
	}


	/**
	 * Maps HTTP request to an array.
	 */
	public function match(Nette\Http\IRequest $httpRequest): ?array
	{
		if ($httpRequest->getUrl()->getPathInfo() !== '') {
			return null;
		}
		// combine with precedence: get, (post,) defaults
		$params = $httpRequest->getQuery();
		$params += $this->defaults;

		$presenter = $params[self::PRESENTER_KEY] ?? null;
		if (!is_string($presenter)) {
			return null;
		}

		$params[self::PRESENTER_KEY] = $this->module . $presenter;
		return $params;
	}


	/**
	 * Constructs absolute URL from array.
	 */
	public function constructUrl(array $params, Nette\Http\Url $refUrl): ?string
	{
		if ($this->flags & self::ONE_WAY) {
			return null;
		}

		// presenter name
		if (strncmp($params[self::PRESENTER_KEY], $this->module, strlen($this->module)) === 0) {
			$params[self::PRESENTER_KEY] = substr($params[self::PRESENTER_KEY], strlen($this->module));
		} else {
			return null;
		}

		// remove default values; null values are retain
		foreach ($this->defaults as $key => $value) {
			if (isset($params[$key]) && $params[$key] == $value) { // intentionally ==
				unset($params[$key]);
			}
		}

		$url = $refUrl->getScheme() . '://' . $refUrl->getAuthority() . $refUrl->getPath();
		$sep = ini_get('arg_separator.input');
		$query = http_build_query($params, '', $sep ? $sep[0] : '&');
		if ($query != '') { // intentionally ==
			$url .= '?' . $query;
		}
		return $url;
	}


	/**
	 * Returns default values.
	 */
	public function getDefaults(): array
	{
		return $this->defaults;
	}


	/**
	 * Returns flags.
	 */
	public function getFlags(): int
	{
		return $this->flags;
	}
}
