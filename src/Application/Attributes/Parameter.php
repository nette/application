<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\Attributes;

use Attribute;


/**
 * Marks property as request parameter. Value is automatically populated from HTTP request and type-cast.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Parameter
{
}
