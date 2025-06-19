<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte;

use Latte;
use Nette;
use Nette\Application\UI;
use function array_unshift, class_exists, is_a, iterator_to_array, preg_match, preg_replace, property_exists, rtrim, version_compare;


/**
 * Latte powered template factory.
 */
class TemplateFactory implements UI\TemplateFactory
{
	/** @var array<callable(Template): void>  Occurs when a new template is created */
	public array $onCreate = [];
	private string $templateClass;


	public function __construct(
		private readonly LatteFactory $latteFactory,
		private readonly ?Nette\Http\IRequest $httpRequest = null,
		private readonly ?Nette\Security\User $user = null,
		private readonly ?Nette\Caching\Storage $cacheStorage = null,
		$templateClass = null,
	) {
		if ($templateClass && (!class_exists($templateClass) || !is_a($templateClass, Template::class, true))) {
			throw new Nette\InvalidArgumentException("Class $templateClass does not implement " . Template::class . ' or it does not exist.');
		}

		$this->templateClass = $templateClass ?: DefaultTemplate::class;
	}


	/** @return Template */
	public function createTemplate(?UI\Control $control = null, ?string $class = null): UI\Template
	{
		$class ??= $this->templateClass;
		if (!is_a($class, Template::class, allow_string: true)) {
			throw new Nette\InvalidArgumentException("Class $class does not implement " . Template::class . ' or it does not exist.');
		}

		$latte = $this->latteFactory->create($control);
		$template = new $class($latte);
		$presenter = $control?->getPresenterIfExists();

		if (version_compare(Latte\Engine::VERSION, '3', '<')) {
			$this->setupLatte2($latte, $control, $presenter, $template);
		} elseif (!Nette\Utils\Arrays::some($latte->getExtensions(), fn($e) => $e instanceof UIExtension)) {
			$latte->addExtension(new UIExtension($control));
		}

		// default parameters
		$baseUrl = $this->httpRequest
			? rtrim($this->httpRequest->getUrl()->withoutUserInfo()->getBaseUrl(), '/')
			: null;
		$flashes = $presenter instanceof UI\Presenter && $presenter->hasFlashSession()
			? (array) $presenter->getFlashSession()->get($control->getParameterId('flash'))
			: [];

		$params = [
			'user' => $this->user,
			'baseUrl' => $baseUrl,
			'basePath' => $baseUrl ? preg_replace('#https?://[^/]+#A', '', $baseUrl) : null,
			'flashes' => $flashes,
			'control' => $control,
			'presenter' => $presenter,
		];

		foreach ($params as $key => $value) {
			if ($value !== null && property_exists($template, $key)) {
				$template->$key = $value;
			}
		}

		Nette\Utils\Arrays::invoke($this->onCreate, $template);

		return $template;
	}


	private function setupLatte2(
		Latte\Engine $latte,
		?UI\Control $control,
		?UI\Presenter $presenter,
		Template $template,
	): void
	{
		if ($latte->onCompile instanceof \Traversable) {
			$latte->onCompile = iterator_to_array($latte->onCompile);
		}

		array_unshift($latte->onCompile, function (Latte\Engine $latte) use ($control, $template): void {
			if ($this->cacheStorage) {
				$latte->getCompiler()->addMacro('cache', new Nette\Bridges\CacheLatte\CacheMacro);
			}

			UIMacros::install($latte->getCompiler());
			if (class_exists(Nette\Bridges\FormsLatte\FormMacros::class)) {
				Nette\Bridges\FormsLatte\FormMacros::install($latte->getCompiler());
			}

			$control?->templatePrepareFilters($template);
		});

		$latte->addProvider('cacheStorage', $this->cacheStorage);

		if ($control) {
			$latte->addProvider('uiControl', $control);
			$latte->addProvider('uiPresenter', $presenter);
			$latte->addProvider('snippetBridge', new SnippetBridge($control));
			if ($presenter) {
				$header = $presenter->getHttpResponse()->getHeader('Content-Security-Policy')
					?: $presenter->getHttpResponse()->getHeader('Content-Security-Policy-Report-Only');
			}

			$nonce = $presenter && preg_match('#\s\'nonce-([\w+/]+=*)\'#', (string) $header, $m) ? $m[1] : null;
			$latte->addProvider('uiNonce', $nonce);
		}

		if ($presenter) {
			$latte->addFunction('isLinkCurrent', [$presenter, 'isLinkCurrent']);
			$latte->addFunction('isModuleCurrent', [$presenter, 'isModuleCurrent']);
		}

		$latte->addFilter('modifyDate', fn($time, $delta, $unit = null) => $time
				? Nette\Utils\DateTime::from($time)->modify($delta . $unit)
				: null);

	}
}
