<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\UI;

if (false) {
	/** @deprecated use Nette\Application\UI\Component */
	class PresenterComponent
	{
	}
} elseif (!class_exists(PresenterComponent::class)) {
	class_alias(Component::class, PresenterComponent::class);
}

if (false) {
	/** @deprecated use Nette\Application\UI\ComponentReflection */
	class PresenterComponentReflection
	{
	}
} elseif (!class_exists(PresenterComponentReflection::class)) {
	class_alias(ComponentReflection::class, PresenterComponentReflection::class);
}
