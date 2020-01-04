<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte;

use Nette;


/**
 * Strict Latte powered template for controls.
 */
class StrictTemplate extends LatteTemplate
{
	use Nette\SmartObject;

	/** @var Nette\Security\User */
	public $user;

	/** @var string */
	public $baseUrl;

	/** @var string */
	public $basePath;

	/** @var array */
	public $flashes = [];

	/** @var Nette\Application\UI\Presenter|null */
	public $presenter;

	/** @var Nette\Application\UI\Control|null */
	public $control;


	/**
	 * Returns array of all parameters.
	 */
	public function getParameters(): array
	{
		return get_object_vars($this);
	}
}
