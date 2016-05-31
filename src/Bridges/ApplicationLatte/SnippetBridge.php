<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Bridges\ApplicationLatte;


use Latte\Runtime\ISnippetBridge;
use Nette\Application\UI\Control;
use Nette\Application\UI\IRenderable;
use Nette\ComponentModel\IContainer;
use Nette\SmartObject;

/**
 * @internal
 */
class SnippetBridge implements ISnippetBridge
{
	use SmartObject;

	/** @var Control */
	private $control;

	/** @var \stdClass|null */
	private $payload;


	public function __construct(Control $control)
	{
		$this->control = $control;
	}

	public function isSnippetMode()
	{
		return $this->control->snippetMode;
	}


	public function needsRedraw($name)
	{
		return $this->control->isControlInvalid($name);
	}


	public function markRedrawn($name)
	{
		if ($name !== "") {
			$this->control->redrawControl($name, FALSE);
		}
	}


	public function getHtmlId($name)
	{
		return $this->control->getSnippetId($name);
	}


	public function addSnippet($name, $content)
	{
		if ($this->payload === NULL) {
			$this->payload = $this->control->getPresenter()->getPayload();
		}
		$this->payload->snippets[$this->control->getSnippetId($name)] = $content;
	}


	public function renderChildren()
	{
		if ($this->control instanceof IRenderable) {
			$queue = [$this->control];
			do {
				foreach (array_shift($queue)->getComponents() as $child) {
					if ($child instanceof IRenderable) {
						if ($child->isControlInvalid()) {
							$child->snippetMode = TRUE;
							$child->render();
							$child->snippetMode = FALSE;
						}
					} elseif ($child instanceof IContainer) {
						$queue[] = $child;
					}
				}
			} while ($queue);
		}
	}


}
