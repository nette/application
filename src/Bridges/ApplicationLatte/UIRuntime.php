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
	public static function initialize(Latte\Template $template, $blockQueue)
	{
		// snippet support
		$params = $template->getParameters();
		if (!$template->getParentName() && !empty($params['_control']->snippetMode)) {
			$tmp = $template;
			while (in_array($tmp->getReferenceType(), ['extends', 'include', NULL], TRUE) && ($tmp = $tmp->getReferringTemplate()));
			if (!$tmp) {
				self::renderSnippets($params['_control'], $blockQueue, $params);
				return TRUE;
			}
		};
	}


	public static function renderSnippets(UI\Control $control, array $blockQueue = NULL, array $params = [])
	{
		$control->snippetMode = FALSE;
		$payload = $control->getPresenter()->getPayload();
		foreach ($blockQueue as $name => $function) {
			if ($name[0] !== '_' || !$control->isControlInvalid((string) substr($name, 1))) {
				continue;
			}
			ob_start(function () {});
			$function = reset($function);
			$snippets = $function($params + ['_snippetMode' => TRUE]);
			$payload->snippets[$id = $control->getSnippetId((string) substr($name, 1))] = ob_get_clean();
			if ($snippets !== NULL) { // pass FALSE from snippetArea
				if ($snippets) {
					$payload->snippets += $snippets;
				}
				unset($payload->snippets[$id]);
			}
		}
		$control->snippetMode = TRUE;
		if ($control instanceof UI\IRenderable) {
			$queue = [$control];
			do {
				foreach (array_shift($queue)->getComponents() as $child) {
					if ($child instanceof UI\IRenderable) {
						if ($child->isControlInvalid()) {
							$child->snippetMode = TRUE;
							$child->render();
							$child->snippetMode = FALSE;
						}
					} elseif ($child instanceof Nette\ComponentModel\IContainer) {
						$queue[] = $child;
					}
				}
			} while ($queue);
		}
	}

}
