<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte\Nodes;

use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\Expression\FilterNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;
use Latte\Context;
use Nette\Utils\Strings;


/**
 * {control name[:method] [params]}
 */
class ControlNode extends StatementNode
{
	public ExpressionNode $name;
	public ?ExpressionNode $method = null;
	public ArrayNode $args;
	public ?FilterNode $filter = null;


	public static function create(Tag $tag): static
	{
		$tag->expectArguments();
		$node = new static;
		$node->name = $tag->parser->parseExpression();
		if ($tag->parser->stream->tryConsume(':')) {
			$node->method = $tag->parser->parseExpression();
		}
		$tag->parser->stream->tryConsume(',');
		$node->args = $tag->parser->parseArguments();
		$node->filter = $tag->parser->parseFilters();
		return $node;
	}


	public function print(PrintContext $context): string
	{
		$filter = $this->filter;
		if (implode('', $context->getEscapingContext()) !== Context::Html) {
			$filter = (string) $filter?->name === 'noescape'
				? $filter->inner
				: FilterNode::escapeFilter($filter);
		}

		$method = match (true) {
			!$this->method => 'render',
			$this->method instanceof StringNode && Strings::match($this->method->value, '#^\w*$#D') => 'render' . ucfirst($this->method->value),
			default => "{'render' . " . $this->method->print($context) . '}',
		};

		return $context->format(
			'%line '
			. ($this->name instanceof StringNode ? '' : 'if (!is_object($_tmp = %1.raw)) ')
			. '$_tmp = $this->global->uiControl->getComponent(%1.raw); '
			. 'if ($_tmp instanceof Nette\Application\UI\Renderable) $_tmp->redrawControl(null, false); '
			. '%2.raw',
			$this->startLine,
			$this->name,
			$context->format(
				$filter
				? 'ob_start(fn() => ""); $_tmp->%raw(%args); $ʟ_fi = new LR\FilterInfo(%dump); echo %modifyContent(ob_get_clean());'
				: '$_tmp->%1.raw(%2.args);',
				$filter,
				$method,
				$this->args,
				Context::Html,
			),
		);
	}


	public function &getIterator(): \Generator
	{
		yield $this->name;
		if ($this->method) {
			yield $this->method;
		}
		yield $this->args;
		if ($this->filter) {
			yield $this->filter;
		}
	}
}
