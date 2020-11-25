<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte;

use Latte\Runtime\ISnippetBridge;
use Nette;
use Nette\Application\UI\Control;
use Nette\Application\UI\Renderable;


/**
 * @internal
 */
class SnippetBridge implements ISnippetBridge
{
	use Nette\SmartObject;

	/** @var Control */
	private $control;

	/** @var \stdClass|null */
	private $payload;


	public function __construct(Control $control)
	{
		$this->control = $control;
	}


	public function isSnippetMode(): bool
	{
		return (bool) $this->control->snippetMode;
	}


	public function setSnippetMode($snippetMode)
	{
		$this->control->snippetMode = $snippetMode;
	}


	public function needsRedraw($name): bool
	{
		return $this->control->isControlInvalid($name);
	}


	public function markRedrawn($name): void
	{
		if ($name !== '') {
			$this->control->redrawControl($name, false);
		}
	}


	public function getHtmlId($name): string
	{
		return $this->control->getSnippetId($name);
	}


	public function addSnippet($name, $content): void
	{
		if ($this->payload === null) {
			$this->payload = $this->control->getPresenter()->getPayload();
		}
		$this->payload->snippets[$this->control->getSnippetId($name)] = $content;
	}


	public function renderChildren(): void
	{
		$queue = [$this->control];
		do {
			foreach (array_shift($queue)->getComponents() as $child) {
				if ($child instanceof Renderable) {
					if ($child->isControlInvalid()) {
						$child->snippetMode = true;
						$child->render();
						$child->snippetMode = false;
					}
				} elseif ($child instanceof Nette\ComponentModel\IContainer) {
					$queue[] = $child;
				}
			}
		} while ($queue);
	}
}
