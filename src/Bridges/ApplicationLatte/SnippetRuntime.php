<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte;

use Nette;
use Nette\Application\UI\Control;
use Nette\Application\UI\Renderable;
use function array_pop, array_shift, end, ob_end_clean, ob_get_clean, ob_start, reset, trigger_error;


/**
 * Latte v3 snippet driver
 * @internal
 */
final class SnippetRuntime
{
	public const
		TypeStatic = 'static',
		TypeDynamic = 'dynamic',
		TypeArea = 'area';

	/** @var array<array{string, bool}> */
	private array $stack = [];
	private int $nestingLevel = 0;
	private bool $renderingSnippets = false;

	private ?\stdClass $payload;


	public function __construct(
		private readonly Control $control,
	) {
	}


	public function enter(string $name, string $type): void
	{
		if (!$this->renderingSnippets) {
			if ($type === self::TypeDynamic && $this->nestingLevel === 0) {
				trigger_error('Dynamic snippets are allowed only inside static snippet/snippetArea.', E_USER_WARNING);
			}

			$this->nestingLevel++;
			return;
		}

		$obStarted = false;
		if (
			($this->nestingLevel === 0 && $this->control->isControlInvalid($name))
			|| ($type === self::TypeDynamic && ($previous = end($this->stack)) && $previous[1] === true)
		) {
			ob_start(fn() => null);
			$this->nestingLevel = $type === self::TypeArea ? 0 : 1;
			$obStarted = true;
		} elseif ($this->nestingLevel > 0) {
			$this->nestingLevel++;
		}

		$this->stack[] = [$name, $obStarted];
		if ($name !== '') {
			$this->control->redrawControl($name, false);
		}
	}


	public function leave(): void
	{
		if (!$this->renderingSnippets) {
			$this->nestingLevel--;
			return;
		}

		[$name, $obStarted] = array_pop($this->stack);
		if ($this->nestingLevel > 0 && --$this->nestingLevel === 0) {
			$content = ob_get_clean();
			$this->payload ??= $this->control->getPresenter()->getPayload();
			$this->payload->snippets[$this->control->getSnippetId($name)] = $content;

		} elseif ($obStarted) { // dynamic snippet wrapper or snippet area
			ob_end_clean();
		}
	}


	public function getHtmlId(string $name): string
	{
		return $this->control->getSnippetId($name);
	}


	/**
	 * @param  Block[]  $blocks
	 * @param  mixed[]  $params
	 */
	public function renderSnippets(array $blocks, array $params): bool
	{
		if ($this->renderingSnippets || !$this->control->snippetMode) {
			return false;
		}

		$this->renderingSnippets = true;
		$this->control->snippetMode = false;
		foreach ($blocks as $name => $block) {
			if (!$this->control->isControlInvalid($name)) {
				continue;
			}

			$function = reset($block->functions);
			$function($params);
		}

		$this->control->snippetMode = true;
		$this->renderChildren();

		$this->renderingSnippets = false;
		return true;
	}


	private function renderChildren(): void
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
