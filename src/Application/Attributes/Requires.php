<?php

declare(strict_types=1);

namespace Nette\Application\Attributes;

use Attribute;


/**
 * Restricts access to actions, signals, or entire presenter. Can enforce HTTP methods (GET, POST, etc.),
 * limit to specific actions, allow only forwarded requests, enforce or bypass same-origin policy (CSRF protection),
 * or require AJAX calls.
 */
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
