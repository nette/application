<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

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

	/** @var callable[]  function (Template $template); Occurs when a new template is created */
	public $onCreate;

	/** @var ILatteFactory */
	private $latteFactory;

	/** @var Nette\Http\IRequest */
	private $httpRequest;

	/** @var Nette\Security\User */
	private $user;

	/** @var Nette\Caching\IStorage */
	private $cacheStorage;

	/** @var string */
	private $templateClass;


	public function __construct(ILatteFactory $latteFactory, Nette\Http\IRequest $httpRequest = null,
		Nette\Security\User $user = null, Nette\Caching\IStorage $cacheStorage = null, $templateClass = null)
	{
		$this->latteFactory = $latteFactory;
		$this->httpRequest = $httpRequest;
		$this->user = $user;
		$this->cacheStorage = $cacheStorage;
		if ($templateClass && (!class_exists($templateClass) || !is_a($templateClass, Template::class, true))) {
			throw new Nette\InvalidArgumentException("Class $templateClass does not extend " . Template::class . ' or it does not exist.');
		}
		$this->templateClass = $templateClass ?: Template::class;
	}


	/**
	 * @return Template
	 */
	public function createTemplate(UI\Control $control = null)
	{
		$latte = $this->latteFactory->create();
		$template = new $this->templateClass($latte);
		$presenter = $control ? $control->getPresenter(false) : null;

		if ($control instanceof UI\Presenter) {
			$latte->setLoader(new Loader($control));
		}

		if ($latte->onCompile instanceof \Traversable) {
			$latte->onCompile = iterator_to_array($latte->onCompile);
		}

		array_unshift($latte->onCompile, function ($latte) use ($control, $template) {
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

		$latte->addFilter('url', 'rawurlencode'); // back compatiblity
		foreach (['normalize', 'toAscii', 'webalize', 'reverse'] as $name) {
			$latte->addFilter($name, 'Nette\Utils\Strings::' . $name);
		}
		$latte->addFilter('null', function () {});
		$latte->addFilter('modifyDate', function ($time, $delta, $unit = null) {
			return $time == null ? null : Nette\Utils\DateTime::from($time)->modify($delta . $unit); // intentionally ==
		});

		if (!isset($latte->getFilters()['translate'])) {
			$latte->addFilter('translate', function (Latte\Runtime\FilterInfo $fi) {
				throw new Nette\InvalidStateException('Translator has not been set. Set translator using $template->setTranslator().');
			});
		}

		// default parameters
		$template->user = $this->user;
		$template->baseUri = $template->baseUrl = $this->httpRequest ? rtrim($this->httpRequest->getUrl()->getBaseUrl(), '/') : null;
		$template->basePath = preg_replace('#https?://[^/]+#A', '', $template->baseUrl);
		$template->flashes = [];
		if ($control) {
			$template->control = $control;
			$template->presenter = $presenter;
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

		// back compatibility
		$template->_control = $control;
		$template->_presenter = $presenter;
		$template->netteCacheStorage = $this->cacheStorage;

		if ($presenter instanceof UI\Presenter && $presenter->hasFlashSession()) {
			$id = $control->getParameterId('flash');
			$template->flashes = (array) $presenter->getFlashSession()->$id;
		}

		$this->onCreate($template);

		return $template;
	}
}
