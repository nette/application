<?php

declare(strict_types=1);

namespace Nette\Application\Attributes;

use Attribute;


/**
 * Marks action, signal, or entire presenter as deprecated.
 * Triggers warning when generating links to deprecated presenter or action.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class Deprecated
{
}
