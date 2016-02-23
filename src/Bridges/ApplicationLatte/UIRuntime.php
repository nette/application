<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Bridges\ApplicationLatte;

use Nette;
use Nette\Application\UI;


/**
 * Runtime helpers for UI macros.
 * @internal
 */
class UIRuntime extends Nette\Object
{

	public static function renderSnippets(UI\Control $control, \stdClass $local, array $params)
	{
		$control->snippetMode = FALSE;
		$payload = $control->getPresenter()->getPayload();
		if (isset($local->blocks)) {
			foreach ($local->blocks as $name => $function) {
				if ($name[0] !== '_' || !$control->isControlInvalid((string) substr($name, 1))) {
					continue;
				}
				ob_start(function () {});
				$function = reset($function);
				$snippets = $function($local, $params + array('_snippetMode' => TRUE));
				$payload->snippets[$id = $control->getSnippetId((string) substr($name, 1))] = ob_get_clean();
				if ($snippets !== NULL) { // pass FALSE from snippetArea
					if ($snippets) {
						$payload->snippets += $snippets;
					}
					unset($payload->snippets[$id]);
				}
			}
		}
		$control->snippetMode = TRUE;
		if ($control instanceof UI\IRenderable) {
			$queue = array($control);
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
