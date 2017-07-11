<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte;

use Latte;
use Nette;


/**
 * Template loader.
 */
class Loader extends Latte\Loaders\FileLoader
{
	/** @var Nette\Application\UI\Presenter */
	private $presenter;


	public function __construct(Nette\Application\UI\Presenter $presenter)
	{
		parent::__construct();
		$this->presenter = $presenter;
	}
}
