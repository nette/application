<?php

declare(strict_types=1);

namespace Nette\Application\Attributes;

use Attribute;


#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Requires
{
	public ?array $methods = null;
	public ?array $actions = null;


	public function __construct(
		string|array|null $methods = null,
		string|array|null $actions = null,
		public ?bool $forward = null,
		public ?bool $sameOrigin = null,
		public ?bool $ajax = null,
	) {
		$this->methods = $methods === null ? null : (array) $methods;
		$this->actions = $actions === null ? null : (array) $actions;
	}
}
