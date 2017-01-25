<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte;

use Nette;
use Latte;


/**
 * Runtime helpers for UI macros.
 * @internal
 */
class UIRuntime
{
	use Nette\StaticClass;

	public static function initialize(Latte\Runtime\Template $template, &$parentName, array $blocks): void
	{
		$providers = $template->global;
		$blocks = array_filter(array_keys($blocks), function ($s) { return $s[0] !== '_'; });
		if ($parentName === NULL && $blocks && !$template->getReferringTemplate()
			&& ($providers->uiControl ?? NULL) instanceof Nette\Application\UI\Presenter
		) {
			$parentName = $providers->uiControl->findLayoutTemplateFile();
		}
	}

}
