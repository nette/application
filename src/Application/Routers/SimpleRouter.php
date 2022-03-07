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
final class SimpleRouter extends Nette\Routing\SimpleRouter implements Nette\Routing\Router
{
	private const
		PresenterKey = 'presenter',
		ModuleKey = 'module';

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
				self::PresenterKey => $presenter,
				'action' => $action === '' ? Application\UI\Presenter::DEFAULT_ACTION : $action,
			];
		}

		if (isset($defaults[self::ModuleKey])) {
			throw new Nette\DeprecatedException(__METHOD__ . '() parameter module is deprecated, use RouteList::withModule() instead.');
		} elseif ($flags) {
			trigger_error(__METHOD__ . '() parameter $flags is deprecated, use RouteList::add(..., $flags) instead.', E_USER_DEPRECATED);
		}

		$this->flags = $flags;
		parent::__construct($defaults);
	}


	/**
	 * Constructs absolute URL from array.
	 */
	public function constructUrl(array $params, Nette\Http\UrlScript $refUrl): ?string
	{
		if ($this->flags & self::ONE_WAY) {
			return null;
		}

		return parent::constructUrl($params, $refUrl);
	}


	/** @deprecated */
	public function getFlags(): int
	{
		trigger_error(__METHOD__ . '() is deprecated.', E_USER_DEPRECATED);
		return $this->flags;
	}
}


interface_exists(Nette\Application\IRouter::class);
