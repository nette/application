<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte;

use Latte;
use Latte\Compiler\Nodes\Php\Expression\AuxiliaryNode;
use Latte\Compiler\Nodes\TemplateNode;
use Latte\Compiler\Tag;
use Latte\Essential\Nodes\ExtendsNode;
use Nette;
use Nette\Application\UI;
use function array_unshift, preg_match;


/**
 * Latte v3 extension for Nette\Application\UI.
 */
final class UIExtension extends Latte\Extension
{
	public function __construct(
		private readonly ?UI\Control $control,
	) {
	}


	public function getFilters(): array
	{
		$presenter = $this->control?->getPresenterIfExists();
		return [
			'modifyDate' => fn($time, $delta, $unit = null) => $time
				? Nette\Utils\DateTime::from($time)->modify($delta . $unit)
				: null,
		] + ($presenter ? [
			'absoluteUrl' => fn(\Stringable|string|null $link): ?string => $link === null
					? null
					: $presenter->getHttpRequest()->getUrl()->resolve((string) $link)->getAbsoluteUrl(),
		] : []);
	}


	public function getFunctions(): array
	{
		if ($presenter = $this->control?->getPresenterIfExists()) {
			return [
				'isLinkCurrent' => $presenter->isLinkCurrent(...),
				'isModuleCurrent' => $presenter->isModuleCurrent(...),
			];
		}
		return [];
	}


	public function getProviders(): array
	{
		$presenter = $this->control?->getPresenterIfExists();
		$httpResponse = $presenter?->getHttpResponse();
		return [
			'coreParentFinder' => $this->findLayoutTemplate(...),
			'uiControl' => $this->control,
			'uiPresenter' => $presenter,
			'snippetDriver' => $this->control ? new SnippetRuntime($this->control) : null,
			'uiNonce' => $httpResponse ? $this->findNonce($httpResponse) : null,
		];
	}


	public function getTags(): array
	{
		return [
			'n:href' => Nodes\LinkNode::create(...),
			'n:nonce' => Nodes\NNonceNode::create(...),
			'control' => Nodes\ControlNode::create(...),
			'plink' => Nodes\LinkNode::create(...),
			'link' => Nodes\LinkNode::create(...),
			'linkBase' => Nodes\LinkBaseNode::create(...),
			'ifCurrent' => Nodes\IfCurrentNode::create(...),
			'templatePrint' => Nodes\TemplatePrintNode::create(...),
			'snippet' => Nodes\SnippetNode::create(...),
			'snippetArea' => Nodes\SnippetAreaNode::create(...),
			'layout' => $this->createExtendsNode(...),
			'extends' => $this->createExtendsNode(...),
		];
	}


	public function getPasses(): array
	{
		return [
			'snippetRendering' => $this->snippetRenderingPass(...),
			'applyLinkBase' => [Nodes\LinkBaseNode::class, 'applyLinkBasePass'],
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
		return $presenter instanceof UI\Presenter && !empty($template::Blocks[$template::LayerTop])
			? $presenter->findLayoutTemplateFile()
			: null;
	}


	private function findNonce(Nette\Http\IResponse $httpResponse): ?string
	{
		$header = $httpResponse->getHeader('Content-Security-Policy')
			?: $httpResponse->getHeader('Content-Security-Policy-Report-Only');
		return preg_match('#\s\'nonce-([\w+/]+=*)\'#', (string) $header, $m) ? $m[1] : null;
	}


	public static function createExtendsNode(Tag $tag): ExtendsNode
	{
		$auto = $tag->parser->stream->is('auto');
		$node = ExtendsNode::create($tag);
		if ($auto) {
			$node->extends = new AuxiliaryNode(fn() => '$this->global->uiPresenter->findLayoutTemplateFile()');
		}
		return $node;
	}
}
