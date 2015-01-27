<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Bridges\ApplicationLatte;

use Nette,
	Nette\Application\UI;


/**
 * Latte powered template factory.
 *
 * @author     David Grudl
 */
class TemplateFactory extends Nette\Object implements UI\ITemplateFactory
{
	/** @var Nette\Bridges\ApplicationLatte\ILatteFactory */
	private $latteFactory;

	/** @var Nette\Http\IRequest */
	private $httpRequest;

	/** @var Nette\Http\IResponse */
	private $httpResponse;

	/** @var Nette\Security\User */
	private $user;

	/** @var Nette\Caching\IStorage */
	private $cacheStorage;


	public function __construct(ILatteFactory $latteFactory, Nette\Http\IRequest $httpRequest = NULL,
		Nette\Http\IResponse $httpResponse = NULL, Nette\Security\User $user = NULL, Nette\Caching\IStorage $cacheStorage = NULL)
	{
		$this->latteFactory = $latteFactory;
		$this->httpRequest = $httpRequest;
		$this->httpResponse = $httpResponse;
		$this->user = $user;
		$this->cacheStorage = $cacheStorage;
	}


	/**
	 * @return Template
	 */
	public function createTemplate(UI\Control $control = NULL)
	{
		$latte = $this->latteFactory->create();
		$template = new Template($latte);
		$presenter = $control ? $control->getPresenter(FALSE) : NULL;

		if ($control instanceof UI\Presenter) {
			$latte->setLoader(new Loader($control));
		}

		if ($latte->onCompile instanceof \Traversable) {
			$latte->onCompile = iterator_to_array($latte->onCompile);
		}

		array_unshift($latte->onCompile, function($latte) use ($control, $template) {
			$latte->getParser()->shortNoEscape = TRUE;
			$latte->getCompiler()->addMacro('cache', new Nette\Bridges\CacheLatte\CacheMacro($latte->getCompiler()));
			UIMacros::install($latte->getCompiler());
			if (class_exists('Nette\Bridges\FormsLatte\FormMacros')) {
				Nette\Bridges\FormsLatte\FormMacros::install($latte->getCompiler());
			}
			if ($control) {
				$control->templatePrepareFilters($template);
			}
		});

		$latte->addFilter('url', 'rawurlencode'); // back compatiblity
		foreach (array('normalize', 'toAscii', 'webalize', 'padLeft', 'padRight', 'reverse') as $name) {
			$latte->addFilter($name, 'Nette\Utils\Strings::' . $name);
		}
		$latte->addFilter('null', function() {});
		$latte->addFilter('length', function($var) {
			return is_string($var) ? Nette\Utils\Strings::length($var) : count($var);
		});
		$latte->addFilter('modifyDate', function($time, $delta, $unit = NULL) {
			return $time == NULL ? NULL : Nette\Utils\DateTime::from($time)->modify($delta . $unit); // intentionally ==
		});

		// default parameters
		$template->control = $template->_control = $control;
		$template->presenter = $template->_presenter = $presenter;
		$template->user = $this->user;
		$template->netteHttpResponse = $this->httpResponse;
		$template->netteCacheStorage = $this->cacheStorage;
		$template->baseUri = $template->baseUrl = $this->httpRequest ? rtrim($this->httpRequest->getUrl()->getBaseUrl(), '/') : NULL;
		$template->basePath = preg_replace('#https?://[^/]+#A', '', $template->baseUrl);
		$template->flashes = array();

		if ($presenter instanceof UI\Presenter && $presenter->hasFlashSession()) {
			$id = $control->getParameterId('flash');
			$template->flashes = (array) $presenter->getFlashSession()->$id;
		}

		return $template;
	}

}
