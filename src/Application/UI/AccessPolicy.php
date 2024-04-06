<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\UI;

use Nette\Application\Attributes;


/**
 * Manages access control to presenter elements based on attributes and built-in rules.
 * @internal
 */
final class AccessPolicy
{
	public function __construct(
		private readonly Component $component,
		private readonly \ReflectionClass|\ReflectionMethod $element,
	) {
	}


	public function checkAccess(): void
	{
		$presenter = $this->component->getPresenter();
		if (
			$this->element instanceof \ReflectionMethod
			&& str_starts_with($this->element->getName(), $this->component::formatSignalMethod(''))
			&& !ComponentReflection::parseAnnotation($this->element, 'crossOrigin')
			&& !$this->element->getAttributes(Attributes\CrossOrigin::class)
			&& !$presenter->getHttpRequest()->isSameSite()
		) {
			$presenter->detectedCsrf();
		}
	}
}
