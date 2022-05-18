<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte\Nodes;

use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\Php\ModifierNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;


/**
 * {link destination [,] [params]}
 * {plink destination [,] [params]}
 * n:href="destination [,] [params]"
 */
class LinkNode extends StatementNode
{
	public ExpressionNode $destination;
	public ArrayNode $args;
	public ModifierNode $modifier;
	public string $mode;


	public static function create(Tag $tag): ?static
	{
		$tag->outputMode = $tag::OutputKeepIndentation;
		$tag->expectArguments();
		$node = new static;
		$node->destination = $tag->parser->parseUnquotedStringOrExpression();
		$tag->parser->stream->tryConsume(',');
		$node->args = $tag->parser->parseArguments();
		$node->modifier = $tag->parser->parseModifier();
		$node->modifier->escape = true;
		$node->modifier->check = false;
		$node->mode = $tag->name;

		if ($tag->isNAttribute()) {
			// move at the beginning
			array_unshift($tag->htmlElement->attributes->children, $node);
			return null;
		}

		return $node;
	}


	public function print(PrintContext $context): string
	{
		if ($this->mode === 'href') {
			$context->beginEscape()->enterHtmlAttribute(null, '"');
			$res = $context->format(
				<<<'XX'
						echo ' href="'; echo %modify($this->global->uiControl->link(%node, %node?)) %line; echo '"';
					XX,
				$this->modifier,
				$this->destination,
				$this->args,
				$this->position,
			);
			$context->restoreEscape();
			return $res;
		}

		return $context->format(
			'echo %modify('
			. ($this->mode === 'plink' ? '$this->global->uiPresenter' : '$this->global->uiControl')
			. '->link(%node, %node?)) %line;',
			$this->modifier,
			$this->destination,
			$this->args,
			$this->position,
		);
	}


	public function &getIterator(): \Generator
	{
		yield $this->destination;
		yield $this->args;
		yield $this->modifier;
	}
}
