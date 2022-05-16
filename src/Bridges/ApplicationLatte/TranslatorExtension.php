<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte;

use Latte;
use Latte\Compiler\NodeHelpers;
use Latte\Compiler\Nodes\Php;
use Latte\Compiler\Tag;
use Latte\Engine;
use Latte\Essential\Nodes\PrintNode;
use Nette\Localization\Translator;


/**
 * Latte v3 extension for translations.
 */
final class TranslatorExtension extends Latte\Extension
{
	public function __construct(
		private ?Translator $translator,
		private ?string $key = null,
	) {
	}


	public function getTags(): array
	{
		return [
			'_' => [$this, 'parseTranslate'],
			'translate' => fn(Tag $tag): \Generator => Nodes\TranslateNode::create($tag, $this->key ? $this->translator : null),
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


	public function getCacheKey(Engine $engine): mixed
	{
		return $this->key;
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

		if ($this->translator && $this->key) {
			try {
				$translation = $this->translator->translate(
					NodeHelpers::toValue($node->expression, constants: true),
					...NodeHelpers::toValue($args, constants: true),
				);
				if (is_string($translation)) {
					$node->expression = new Php\Scalar\StringNode($translation);
					return $node;
				}
			} catch (\InvalidArgumentException) {
			}
		}

		array_unshift($node->modifier->filters, new Php\FilterNode(new Php\IdentifierNode('translate'), $args->toArguments()));
		return $node;
	}
}
