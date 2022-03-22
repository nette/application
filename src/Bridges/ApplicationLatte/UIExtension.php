<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte;

use Latte;
use Latte\Compiler\Nodes\TemplateNode;
use Nette;
use Nette\Application\UI;


/**
 * Latte v3 extension for Nette\Application\UI.
 */
final class UIExtension extends Latte\Extension
{
	public function __construct(
		private ?UI\Control $control,
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
		$presenter = $this->control?->getPresenterIfExists();
		$httpResponse = $presenter?->getHttpResponse();
		return [
			'coreParentFinder' => [$this, 'findLayoutTemplate'],
			'uiControl' => $this->control,
			'uiPresenter' => $presenter,
			'snippetDriver' => $this->control ? new SnippetDriver($this->control) : null,
			'uiNonce' => $httpResponse ? $this->findNonce($httpResponse) : null,
		];
	}


	public function getTags(): array
	{
		return [
			'n:href' => [Nodes\LinkNode::class, 'create'],
			'n:nonce' => [Nodes\NNonceNode::class, 'create'],
			'control' => [Nodes\ControlNode::class, 'create'],
			'plink' => [Nodes\LinkNode::class, 'create'],
			'link' => [Nodes\LinkNode::class, 'create'],
			'ifCurrent' => [Nodes\IfCurrentNode::class, 'create'],
			'templatePrint' => [Nodes\TemplatePrintNode::class, 'create'],
			'snippet' => [Nodes\SnippetNode::class, 'create'],
			'snippetArea' => [Nodes\SnippetAreaNode::class, 'create'],
		];
	}


	public function getPasses(): array
	{
		return [
			'snippetRendering' => [$this, 'snippetRenderingPass'],
		];
	}


	/**
	 * Render snippets instead of template in snippet-mode.
	 */
	public function snippetRenderingPass(TemplateNode $templateNode): void
	{
		array_unshift($templateNode->main->children, new Latte\Compiler\Nodes\AuxiliaryNode(fn() => <<<'XX'
			if ($this->global->snippetDriver?->renderSnippets($this->blocks[self::LayerSnippet], $this->params)) { return; }


			XX));
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


	private function findNonce(Nette\Http\IResponse $httpResponse): ?string
	{
		$header = $httpResponse->getHeader('Content-Security-Policy')
			?: $httpResponse->getHeader('Content-Security-Policy-Report-Only');
		return preg_match('#\s\'nonce-([\w+/]+=*)\'#', (string) $header, $m) ? $m[1] : null;
	}
}
