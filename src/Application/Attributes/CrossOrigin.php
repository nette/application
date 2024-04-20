<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\Attributes;

use Attribute;


/**
 * Use Requires(sameOrigin: false)
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class CrossOrigin extends Requires
{
	public function __construct()
	{
		parent::__construct(sameOrigin: false);
	}
}
