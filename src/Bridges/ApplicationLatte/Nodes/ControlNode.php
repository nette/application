<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte\Nodes;

use Latte;
use Latte\Compiler\Escaper;
use Latte\Compiler\Nodes\Php\Expression\ArrayItemNode;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\Php\ModifierNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;
use Nette\Utils\Strings;


/**
 * {control name[:method] [params]}
 */
class ControlNode extends StatementNode
{
	public ExpressionNode $name;
	public ?ExpressionNode $method = null;
	public ArrayNode $args;
	public ?bool $escape = null;


	public static function create(Tag $tag): static
	{
		$tag->outputMode = $tag::OutputRemoveIndentation;
		$tag->expectArguments();
		$stream = $tag->parser->stream;
		$node = new static;
		$node->name = $tag->parser->parseUnquotedStringOrExpression(colon: false);
		if ($stream->tryConsume(':')) {
			$node->method = $tag->parser->parseExpression();
		}

		$stream->tryConsume(',');
		$start = $stream->getIndex();
		$node->args = $tag->parser->parseArguments();
		$start -= $stream->getIndex();
		$depth = $wrap = null;
		for (; $start < 0; $start++) {
			$token = $stream->peek($start);
			match (true) {
				$token->is('[') => $depth++,
				$token->is(']') => $depth--,
				$token->is('=>') && !$depth => $wrap = true,
				default => null,
			};
		}

		if ($wrap) {
			$node->args = new ArrayNode([new ArrayItemNode($node->args)]);
		}

		$modifier = $tag->parser->parseModifier();
		foreach ($modifier->filters as $filter) {
			match ($filter->name->name) {
				'noescape' => $node->escape = false,
				default => throw new Latte\CompileException('Only modifier |noescape is allowed here.', $tag->position),
			};
		}

		return $node;
	}


	public function print(PrintContext $context): string
	{
		if ($this->escape === null && $context->getEscaper()->getState() !== Escaper::HtmlText) {
			$this->escape = true;
		}

		$method = match (true) {
			!$this->method => 'render',
			$this->method instanceof StringNode && Strings::match($this->method->value, '#^\w*$#D') => 'render' . ucfirst($this->method->value),
			default => "{'render' . " . $this->method->print($context) . '}',
		};

		$fetchCode = $context->format(
			$this->name instanceof StringNode
				? '$ʟ_tmp = $this->global->uiControl->getComponent(%node);'
				: 'if (!is_object($ʟ_tmp = %node)) $ʟ_tmp = $this->global->uiControl->getComponent($ʟ_tmp);',
			$this->name,
		);

		if ($this->escape) {
			return $context->format(
				<<<'XX'
					%raw
					if ($ʟ_tmp instanceof Nette\Application\UI\Renderable) $ʟ_tmp->redrawControl(null, false);
					ob_start(fn() => '');
					$ʟ_tmp->%raw(%args) %line;
					$ʟ_fi = new LR\FilterInfo(%dump); echo %modifyContent(ob_get_clean());


					XX,
				$fetchCode,
				$method,
				$this->args,
				$this->position,
				Latte\ContentType::Html,
				new ModifierNode([], $this->escape),
			);

		} else {
			return $context->format(
				<<<'XX'
					%raw
					if ($ʟ_tmp instanceof Nette\Application\UI\Renderable) $ʟ_tmp->redrawControl(null, false);
					$ʟ_tmp->%raw(%args) %line;


					XX,
				$fetchCode,
				$method,
				$this->args,
				$this->position,
			);
		}
	}


	public function &getIterator(): \Generator
	{
		yield $this->name;
		if ($this->method) {
			yield $this->method;
		}
		yield $this->args;
	}
}
