<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte\Nodes;

use Latte\CompileException;
use Latte\Compiler\Block;
use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\AuxiliaryNode;
use Latte\Compiler\Nodes\Html\ElementNode;
use Latte\Compiler\Nodes\Php;
use Latte\Compiler\Nodes\Php\Expression;
use Latte\Compiler\Nodes\Php\Scalar;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;
use Latte\Compiler\TemplateParser;
use Latte\Runtime\Template;
use Nette\Bridges\ApplicationLatte\SnippetDriver;


/**
 * {snippet [name]}
 */
class SnippetNode extends StatementNode
{
	public static string $snippetAttribute = 'id';
	public Block $block;
	public AreaNode $content;
	public ?ElementNode $htmlElement;


	/** @return \Generator<int, ?array, array{AreaNode, ?Tag}, static> */
	public static function create(Tag $tag, TemplateParser $parser): \Generator
	{
		$tag->outputMode = $tag::OutputKeepIndentation;

		$node = new static;
		$node->htmlElement = $tag->isNAttribute() ? $tag->htmlElement : null;

		if ($tag->parser->isEnd()) {
			$name = null;
			$node->block = new Block(new Scalar\StringNode(''), Template::LayerSnippet, $tag);
		} else {
			$name = $tag->parser->parseUnquotedStringOrExpression();
			if (
				$name instanceof Expression\ClassConstantFetchNode
				&& $name->class instanceof Php\NameNode
				&& $name->name instanceof Php\IdentifierNode
			) {
				$name = new Scalar\StringNode(constant($name->class . '::' . $name->name), $name->position);
			}
			$node->block = new Block($name, Template::LayerSnippet, $tag);
			if (!$node->block->isDynamic()) {
				$parser->checkBlockIsUnique($node->block);
			}
		}

		if ($tag->isNAttribute()) {
			if ($tag->prefix !== $tag::PrefixNone) {
				throw new CompileException("Use n:snippet instead of {$tag->getNotation()}", $tag->position);

			} elseif ($tag->htmlElement->getAttribute(self::$snippetAttribute)) {
				throw new CompileException('Cannot combine HTML attribute ' . self::$snippetAttribute . ' with n:snippet.', $tag->position);

			} elseif (isset($tag->htmlElement->nAttributes['ifcontent'])) {
				throw new CompileException('Cannot combine n:ifcontent with n:snippet.', $tag->position);

			} elseif (isset($tag->htmlElement->nAttributes['foreach'])) {
				throw new CompileException('Combination of n:snippet with n:foreach is invalid, use n:inner-foreach.', $tag->position);
			}

			$tag->replaceNAttribute(new AuxiliaryNode(
				fn(PrintContext $context) => "echo ' " . $node->printAttribute($context) . "';",
			));
		}

		[$node->content, $endTag] = yield;
		if ($endTag && $name instanceof Scalar\StringNode) {
			$endTag->parser->stream->tryConsume($name->value);
		}

		return $node;
	}


	public function print(PrintContext $context): string
	{
		if (!$this->block->isDynamic()) {
			$context->addBlock($this->block);
		}

		if ($this->htmlElement) {
			try {
				$inner = $this->htmlElement->content;
				$this->htmlElement->content = new AuxiliaryNode(fn() => $this->printContent($context, $inner));
				return $this->content->print($context);
			} finally {
				$this->htmlElement->content = $inner;
			}
		} else {
			return <<<XX
				echo '<div {$this->printAttribute($context)}>';
				{$this->printContent($context, $this->content)}
				echo '</div>';
				XX;
		}
	}


	private function printContent(PrintContext $context, AreaNode $inner): string
	{
		$dynamic = $this->block->isDynamic();
		$res = $context->format(
			<<<'XX'
				$this->global->snippetDriver->enter(%node, %dump) %line;
				try {
					%node
				} finally {
					$this->global->snippetDriver->leave();
				}

				XX,
			$dynamic ? new AuxiliaryNode(fn() => '$ʟ_nm') : $this->block->name,
			$dynamic ? SnippetDriver::TypeDynamic : SnippetDriver::TypeStatic,
			$this->position,
			$inner,
		);

		if ($dynamic) {
			return $res;
		}

		$this->block->content = $res;
		return $context->format(
			'$this->renderBlock(%node, [], null, %dump) %line;',
			$this->block->name,
			Template::LayerSnippet,
			$this->position,
		);
	}


	private function printAttribute(PrintContext $context): string
	{
		return $context->format(
			<<<'XX'
				%raw="', htmlspecialchars($this->global->snippetDriver->getHtmlId(%node)), '"
				XX,
			self::$snippetAttribute,
			$this->block->isDynamic()
				? new Expression\AssignNode(new Expression\VariableNode('ʟ_nm'), $this->block->name)
				: $this->block->name,
		);
	}


	public function &getIterator(): \Generator
	{
		yield $this->block->name;
		yield $this->content;
	}
}
