<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Bridges\ApplicationLatte;

use Nette;
use Nette\Application\UI;
use Latte;


/**
 * Runtime helpers for UI macros.
 * @internal
 */
class UIRuntime
{
	use Nette\StaticClass;

	/**
	 * @return bool
	 */
	public static function initialize(Latte\Template $template)
	{
		// back compatiblity
		$params = $template->getParameters();
		if (empty($template->global->uiControl) && isset($params['_control'])) {
			trigger_error('Replace template variable $_control with provider: $latte->addProvider("uiControl", ...)', E_USER_DEPRECATED);
			$template->global->uiControl = $params['_control'];
		}
		if (empty($template->global->uiPresenter) && isset($params['_presenter'])) {
			trigger_error('Replace template variable $_presenter with provider: $latte->addProvider("uiPresenter", ...)', E_USER_DEPRECATED);
			$template->global->uiPresenter = $params['_presenter'];
		}
	}

}
