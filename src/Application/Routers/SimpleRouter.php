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
	private const PresenterKey = 'presenter';


	public function __construct(array $defaults = [])
	{
		if (is_string($defaults)) {
			[$presenter, $action] = Nette\Application\Helpers::splitName($defaults);
			if (!$presenter) {
				throw new Nette\InvalidArgumentException("Argument must be array or string in format Presenter:action, '$defaults' given.");
			}

			$defaults = [
				self::PresenterKey => $presenter,
				'action' => $action === '' ? Application\UI\Presenter::DefaultAction : $action,
			];
		}

		parent::__construct($defaults);
	}
}


interface_exists(Nette\Application\IRouter::class);
