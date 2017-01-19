<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte;

use Nette;
use Nette\Application\UI;
use Latte;


/**
 * Latte powered template factory.
 */
class TemplateFactory implements UI\ITemplateFactory
{
	use Nette\SmartObject;

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


	public function __construct(ILatteFactory $latteFactory, Nette\Http\IRequest $httpRequest = NULL,
		Nette\Security\User $user = NULL, Nette\Caching\IStorage $cacheStorage = NULL, $templateClass = NULL)
	{
		$this->latteFactory = $latteFactory;
		$this->httpRequest = $httpRequest;
		$this->user = $user;
		$this->cacheStorage = $cacheStorage;
		if ($templateClass && (!class_exists($templateClass) || !is_a($templateClass, Template::class, TRUE))) {
			throw new Nette\InvalidArgumentException("Class $templateClass does not extend " . Template::class . ' or it does not exist.');
		}
		$this->templateClass = $templateClass ?: Template::class;
	}


	public function createTemplate(UI\Control $control = NULL): UI\ITemplate
	{
		$latte = $this->latteFactory->create();
		$template = new $this->templateClass($latte);
		$presenter = $control ? $control->getPresenter(FALSE) : NULL;

		if ($control instanceof UI\Presenter) {
			$latte->setLoader(new Loader($control));
		}

		if ($latte->onCompile instanceof \Traversable) {
			$latte->onCompile = iterator_to_array($latte->onCompile);
		}

		array_unshift($latte->onCompile, function ($latte) use ($control, $template) {
			if ($this->cacheStorage) {
				$latte->getCompiler()->addMacro('cache', new Nette\Bridges\CacheLatte\CacheMacro($latte->getCompiler()));
			}
			UIMacros::install($latte->getCompiler());
			if (class_exists(Nette\Bridges\FormsLatte\FormMacros::class)) {
				Nette\Bridges\FormsLatte\FormMacros::install($latte->getCompiler());
			}
			if ($control) {
				$control->templatePrepareFilters($template);
			}
		});

		$latte->addFilter('webalize', 'Nette\Utils\Strings::webalize');
		$latte->addFilter('url', function ($s) {
			trigger_error('Filter |url is deprecated, use |escapeUrl.', E_USER_DEPRECATED);
			return rawurlencode($s);
		});
		foreach (['normalize', 'toAscii', 'reverse'] as $name) {
			$latte->addFilter($name, function ($s) use ($name) {
				trigger_error("Filter |$name is deprecated.", E_USER_DEPRECATED);
				return [Nette\Utils\Strings::class, $name]($s);
			});
		}
		$latte->addFilter('null', function () {
			trigger_error('Filter |null is deprecated.', E_USER_DEPRECATED);
		});
		$latte->addFilter('modifyDate', function ($time, $delta, $unit = NULL) {
			trigger_error('Filter |modifyDate is deprecated.', E_USER_DEPRECATED);
			return $time == NULL ? NULL : Nette\Utils\DateTime::from($time)->modify($delta . $unit); // intentionally ==
		});

		if (!isset($latte->getFilters()['translate'])) {
			$latte->addFilter('translate', function (Latte\Runtime\FilterInfo $fi) {
				throw new Nette\InvalidStateException('Translator has not been set. Set translator using $template->setTranslator().');
			});
		}

		// default parameters
		$template->user = $this->user;
		$template->baseUri = $template->baseUrl = $this->httpRequest ? rtrim($this->httpRequest->getUrl()->getBaseUrl(), '/') : NULL;
		$template->basePath = preg_replace('#https?://[^/]+#A', '', $template->baseUrl);
		$template->flashes = [];
		if ($control) {
			$template->control = $control;
			$template->presenter = $presenter;
			$latte->addProvider('uiControl', $control);
			$latte->addProvider('uiPresenter', $presenter);
			$latte->addProvider('snippetBridge', new Nette\Bridges\ApplicationLatte\SnippetBridge($control));
			$nonce = preg_match('#\s\'nonce-([\w+/]+=*)\'#', (string) $presenter->getHttpResponse()->getHeader('Content-Security-Policy'), $m) ? $m[1] : NULL;
			$latte->addProvider('uiNonce', $nonce);
		}
		$latte->addProvider('cacheStorage', $this->cacheStorage);

		if ($presenter instanceof UI\Presenter && $presenter->hasFlashSession()) {
			$id = $control->getParameterId('flash');
			$template->flashes = (array) $presenter->getFlashSession()->$id;
		}

		return $template;
	}

}
