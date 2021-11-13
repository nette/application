<?php

declare(strict_types=1);

namespace Nette\Application\Attributes;

use Attribute;


#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class AllowedFor
{
	public function __construct(
		public ?bool $httpGet = null,
		public ?bool $httpPost = null,
		public ?bool $forward = null,
		public ?array $actions = null,
		public ?bool $crossOrigin = null,
	) {
	}
}
