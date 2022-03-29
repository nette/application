<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte\Nodes;

use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\FragmentNode;
use Latte\Compiler\Nodes\NopNode;
use Latte\Compiler\Nodes\Php\Expression\FilterNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\Nodes\TextNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;


/**
 * {translate} ... {/translate}
 */
class TranslateNode extends StatementNode
{
	public AreaNode $content;
	public ?FilterNode $filter;


	/** @return \Generator<int, ?array, array{AreaNode, ?Tag}, static|NopNode> */
	public static function create(Tag $tag): \Generator
	{
		$tag->outputMode = $tag::OutputKeepIndentation;

		$node = new static;
		$node->filter = $tag->parser->parseFilters();
		if ($tag->void) {
			return new NopNode;
		}
		[$node->content] = yield;
		return $node;
	}


	public function print(PrintContext $context): string
	{
		$filter = (string) $this->filter?->name === 'noescape'
			? $this->filter->inner
			: FilterNode::escapeFilter($this->filter);

		if (
			$this->content instanceof FragmentNode
			&& count($this->content->children) === 1
			&& $this->content->children[0] instanceof TextNode
		) {
			return $context->format(
				<<<'XX'
					$ʟ_fi = new LR\FilterInfo(%dump);
					echo %modifyContent($this->filters->filterContent('translate', $ʟ_fi, %dump)) %line;
					XX,
				$filter,
				implode('', $context->getEscapingContext()),
				$this->content->children[0]->content,
				$this->startLine,
			);

		} else {
			return $context->format(
				<<<'XX'
					ob_start(fn() => ''); try {
						%raw
					} finally {
						$ʟ_tmp = ob_get_clean();
					}
					$ʟ_fi = new LR\FilterInfo(%dump);
					echo %modifyContent($this->filters->filterContent('translate', $ʟ_fi, $ʟ_tmp)) %line;
					XX,
				$filter,
				$this->content,
				implode('', $context->getEscapingContext()),
				$this->startLine,
			);
		}
	}


	public function &getIterator(): \Generator
	{
		yield $this->content;
		if ($this->filter) {
			yield $this->filter;
		}
	}
}
