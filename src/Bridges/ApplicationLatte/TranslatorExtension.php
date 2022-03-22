<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte;

use Latte;
use Latte\Compiler\Nodes\Php;
use Latte\Compiler\Tag;
use Latte\Essential\Nodes\PrintNode;
use Nette\Localization\Translator;


/**
 * Latte v3 extension for translations.
 */
final class TranslatorExtension extends Latte\Extension
{
	public function __construct(
		private ?Translator $translator,
	) {
	}


	public function getTags(): array
	{
		return [
			'_' => [$this, 'parseTranslate'],
			'translate' => [Nodes\TranslateNode::class, 'create'],
		];
	}


	public function getFilters(): array
	{
		return [
			'translate' => fn(Latte\Runtime\FilterInfo $fi, ...$args): string => $this->translator === null
					? $args[0]
					: $this->translator->translate(...$args),
		];
	}


	/**
	 * {_ ...}
	 */
	public function parseTranslate(Tag $tag): PrintNode
	{
		$tag->outputMode = $tag::OutputKeepIndentation;
		$tag->expectArguments();
		$node = new PrintNode;
		$node->expression = $tag->parser->parseExpression();
		$args = new Php\Expression\ArrayNode;
		if ($tag->parser->stream->tryConsume(',')) {
			$args = $tag->parser->parseArguments();
		}
		$node->modifier = $tag->parser->parseModifier();
		$node->modifier->escape = true;
		array_unshift($node->modifier->filters, new Php\FilterNode(new Php\IdentifierNode('translate'), $args->toArguments()));
		return $node;
	}
}
