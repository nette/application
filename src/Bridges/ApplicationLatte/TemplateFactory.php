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


/**
 * Latte powered template factory.
 */
class TemplateFactory implements UI\ITemplateFactory
{
	use Nette\SmartObject;

	/** @var callable[]&(callable(Template $template): void)[]; Occurs when a new template is created */
	public $onCreate;

	/** @var ILatteFactory */
	private $latteFactory;

	/** @var Nette\Http\IRequest|null */
	private $httpRequest;

	/** @var Nette\Security\User|null */
	private $user;

	/** @var Nette\Caching\IStorage|null */
	private $cacheStorage;

	/** @var string */
	private $templateClass;


	public function __construct(
		ILatteFactory $latteFactory,
		Nette\Http\IRequest $httpRequest = null,
		Nette\Security\User $user = null,
		Nette\Caching\IStorage $cacheStorage = null,
		$templateClass = null
	) {
		$this->latteFactory = $latteFactory;
		$this->httpRequest = $httpRequest;
		$this->user = $user;
		$this->cacheStorage = $cacheStorage;
		if ($templateClass && (!class_exists($templateClass) || !is_a($templateClass, Template::class, true))) {
			throw new Nette\InvalidArgumentException("Class $templateClass does not extend " . Template::class . ' or it does not exist.');
		}
		$this->templateClass = $templateClass ?: DefaultTemplate::class;
	}


	public function createTemplate(UI\Control $control = null): UI\ITemplate
	{
		$latte = $this->latteFactory->create();
		$template = new $this->templateClass($latte);
		$presenter = $control ? $control->getPresenterIfExists() : null;

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
			if ($control) {
				$control->templatePrepareFilters($template);
			}
		});

		$latte->addFilter('modifyDate', function ($time, $delta, $unit = null) {
			return $time
				? Nette\Utils\DateTime::from($time)->modify($delta . $unit)
				: null;
		});

		if (!isset($latte->getFilters()['translate'])) {
			$latte->addFilter('translate', function (Latte\Runtime\FilterInfo $fi): void {
				throw new Nette\InvalidStateException('Translator has not been set. Set translator using $template->setTranslator().');
			});
		}

		if ($presenter) {
			$latte->addFunction('isLinkCurrent', [$presenter, 'isLinkCurrent']);
			$latte->addFunction('isModuleCurrent', [$presenter, 'isModuleCurrent']);
		}

		// default parameters
		$baseUrl = $this->httpRequest
			? rtrim($this->httpRequest->getUrl()->withoutUserInfo()->getBaseUrl(), '/')
			: null;
		$flashes = $presenter instanceof UI\Presenter && $presenter->hasFlashSession()
			? (array) $presenter->getFlashSession()->{$control->getParameterId('flash')}
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

		if ($control) {
			$latte->addProvider('uiControl', $control);
			$latte->addProvider('uiPresenter', $presenter);
			$latte->addProvider('snippetBridge', new Nette\Bridges\ApplicationLatte\SnippetBridge($control));
			if ($presenter) {
				$header = $presenter->getHttpResponse()->getHeader('Content-Security-Policy')
					?: $presenter->getHttpResponse()->getHeader('Content-Security-Policy-Report-Only');
			}
			$nonce = $presenter && preg_match('#\s\'nonce-([\w+/]+=*)\'#', (string) $header, $m) ? $m[1] : null;
			$latte->addProvider('uiNonce', $nonce);
		}
		$latte->addProvider('cacheStorage', $this->cacheStorage);

		$this->onCreate($template);

		return $template;
	}
}
