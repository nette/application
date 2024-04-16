<?php

declare(strict_types=1);

namespace Nette\Application\Attributes;

use Attribute;


#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class Deprecated
{
}
