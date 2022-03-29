<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte;

use Latte;
use Latte\Compiler\Nodes\Php\Expression\FilterNode;
use Latte\Compiler\Nodes\Php\IdentifierNode;
use Latte\Compiler\Tag;
use Latte\Compiler\TemplateParser;
use Latte\Essential\Nodes\PrintNode;
use Nette\Application\UI;


/**
 * Latte v3 extension for Nette\Application\UI.
 */
final class UIExtension extends Latte\Extension
{
	public function __construct(
		private ?UI\Control $control = null,
	) {
	}


	public function getFunctions(): array
	{
		if ($presenter = $this->control?->getPresenterIfExists()) {
			return [
				'isLinkCurrent' => [$presenter, 'isLinkCurrent'],
				'isModuleCurrent' => [$presenter, 'isModuleCurrent'],
			];
		}
		return [];
	}


	public function getProviders(): array
	{
		$providers['coreParentFinder'] = [$this, 'findLayoutTemplate'];

		if ($this->control) {
			$providers['snippetDriver'] = new SnippetDriver(new SnippetBridge($this->control));
			$providers['uiControl'] = $this->control;

			if ($presenter = $this->control->getPresenterIfExists()) {
				$providers['uiPresenter'] = $presenter;
				$response = $presenter->getHttpResponse();
				$header = $response->getHeader('Content-Security-Policy') ?: $response->getHeader('Content-Security-Policy-Report-Only');
				$providers['uiNonce'] = preg_match('#\s\'nonce-([\w+/]+=*)\'#', (string) $header, $m) ? $m[1] : null;
			}
		}

		return $providers;
	}


	public function getTags(): array
	{
		return [
			'n:href' => [Nodes\LinkNode::class, 'create'],
			'n:nonce' => [Nodes\NNonceNode::class, 'create'],

			'_' => [$this, 'parseTranslate'],
			'translate' => [Nodes\TranslateNode::class, 'create'],

			'control' => [Nodes\ControlNode::class, 'create'],
			'plink' => [Nodes\LinkNode::class, 'create'],
			'link' => [Nodes\LinkNode::class, 'create'],
			'ifCurrent' => fn() => trigger_error('Tag {ifCurrent} is deprecated, use {if isLinkCurrent()} instead.', E_USER_DEPRECATED),
			'templatePrint' => [Nodes\TemplatePrintNode::class, 'create'],
			'snippet' => [Nodes\SnippetNode::class, 'create'],
			'snippetArea' => [Nodes\SnippetAreaNode::class, 'create'],
		];
	}


	public static function findLayoutTemplate(Latte\Runtime\Template $template): ?string
	{
		$presenter = $template->global->uiControl ?? null;
		return $presenter instanceof UI\Presenter
			&& ($template::Blocks[$template::LayerTop] ?? null)
			&& !$template->getReferringTemplate()
				? $presenter->findLayoutTemplateFile()
				: null;
	}


	/**
	 * {_ ...}
	 */
	public static function parseTranslate(Tag $tag, TemplateParser $parser): PrintNode
	{
		$node = PrintNode::create($tag, $parser);
		$filter = &$node->filter;
		while ($filter) {
			$filter = &$filter->inner;
		}

		$filter = new FilterNode(null, new IdentifierNode('translate'));
		return $node;
	}
}
