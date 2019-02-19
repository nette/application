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
final class SimpleRouter extends Nette\Routing\SimpleRouter implements Nette\Application\IRouter
{
	private const
		PRESENTER_KEY = 'presenter',
		MODULE_KEY = 'module';

	/** @var string */
	private $module = '';

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
			trigger_error(__METHOD__ . '() parameter module is deprecated, use RouteList::withModule() instead.', E_USER_DEPRECATED);
			$this->module = $defaults[self::MODULE_KEY] . ':';
			unset($defaults[self::MODULE_KEY]);
		}

		$this->flags = $flags;
		parent::__construct($defaults);
	}


	/**
	 * Maps HTTP request to an array.
	 */
	public function match(Nette\Http\IRequest $httpRequest): ?array
	{
		$params = parent::match($httpRequest);
		$presenter = $params[self::PRESENTER_KEY] ?? null;
		if (is_string($presenter)) {
			$params[self::PRESENTER_KEY] = $this->module . $presenter;
		}

		return $params;
	}


	/**
	 * Constructs absolute URL from array.
	 */
	public function constructUrl(array $params, Nette\Http\UrlScript $refUrl): ?string
	{
		if ($this->flags & self::ONE_WAY) {
			return null;
		}

		if (strncmp($params[self::PRESENTER_KEY], $this->module, strlen($this->module)) !== 0) {
			return null;
		}
		$params[self::PRESENTER_KEY] = substr($params[self::PRESENTER_KEY], strlen($this->module));
		return parent::constructUrl($params, $refUrl);
	}


	/**
	 * Returns flags.
	 */
	public function getFlags(): int
	{
		return $this->flags;
	}
}
