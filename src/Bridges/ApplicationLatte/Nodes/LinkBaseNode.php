<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte\Nodes;

use Latte\CompileException;
use Latte\Compiler\Node;
use Latte\Compiler\NodeHelpers;
use Latte\Compiler\Nodes\Php\Expression\AuxiliaryNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\Nodes\TemplateNode;
use Latte\Compiler\NodeTraverser;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;
use Nette\Application\LinkGenerator;


/**
 * {linkBase module}
 */
class LinkBaseNode extends StatementNode
{
	public ExpressionNode $base;


	public static function create(Tag $tag): static
	{
		$tag->expectArguments();
		if (!$tag->isInHead()) {
			throw new CompileException("{{$tag->name}} must be placed in template head.", $tag->position);
		}

		$node = new static;
		$node->base = $tag->parser->parseUnquotedStringOrExpression();
		return $node;
	}


	public function print(PrintContext $context): string
	{
		return '';
	}


	public function &getIterator(): \Generator
	{
		yield $this->base;
	}


	public static function applyLinkBasePass(TemplateNode $node): void
	{
		$base = NodeHelpers::findFirst($node, fn(Node $node) => $node instanceof self)?->base;
		if ($base === null) {
			return;
		}

		(new NodeTraverser)->traverse($node, function (Node $link) use ($base) {
			if ($link instanceof LinkNode) {
				if ($link->destination instanceof StringNode && $base instanceof StringNode) {
					$link->destination->value = LinkGenerator::applyBase($link->destination->value, $base->value);
				} else {
					$origDestination = $link->destination;
					$link->destination = new AuxiliaryNode(
						fn(PrintContext $context) => $context->format(
							LinkGenerator::class . '::applyBase(%node, %node)',
							$origDestination,
							$base,
						),
					);
				}
			}
		});
	}
}
