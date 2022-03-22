<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte\Nodes;

use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;


/**
 * {ifCurrent destination [,] [params]}
 * @deprecated use {if isLinkCurrent('...')}
 */
class IfCurrentNode extends StatementNode
{
	public ?ExpressionNode $destination = null;
	public ?ArrayNode $args = null;
	public AreaNode $content;


	public static function create(Tag $tag): \Generator
	{
		trigger_error("Tag {ifCurrent} is deprecated, use {if isLinkCurrent('...')} instead (on line {$tag->position->line})", E_USER_DEPRECATED);
		$node = new static;
		if (!$tag->parser->isEnd()) {
			$node->destination = $tag->parser->parseUnquotedStringOrExpression();
			$tag->parser->stream->tryConsume(',');
			$node->args = $tag->parser->parseArguments();
		}

		[$node->content] = yield;
		return $node;
	}


	public function print(PrintContext $context): string
	{
		return $this->destination
			? $context->format(
				'if ($this->global->uiPresenter->isLinkCurrent(%node, %args?)) { %node } ',
				$this->destination,
				$this->args,
				$this->content,
			)
			: $context->format(
				'if ($this->global->uiPresenter->getLastCreatedRequestFlag("current")) { %node } ',
				$this->content,
			);
	}


	public function &getIterator(): \Generator
	{
		if ($this->destination) {
			yield $this->destination;
			yield $this->args;
		}
		yield $this->content;
	}
}
