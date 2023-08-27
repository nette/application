<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte;

use Latte;
use Latte\Bridges\DI\LatteFactory;
use Nette;
use Nette\Application\UI;


/**
 * Latte powered template factory.
 */
class TemplateFactory implements UI\TemplateFactory
{
	use Nette\SmartObject;

	/** @var array<callable(Template): void>  Occurs when a new template is created */
	public array $onCreate = [];
	private string $templateClass;


	public function __construct(
		private LatteFactory $latteFactory,
		private ?Nette\Http\IRequest $httpRequest = null,
		private ?Nette\Security\User $user = null,
		private ?Nette\Caching\Storage $cacheStorage = null,
		$templateClass = null,
		private array $configVars = [],
	) {
		if ($templateClass && (!class_exists($templateClass) || !is_a($templateClass, Template::class, true))) {
			throw new Nette\InvalidArgumentException("Class $templateClass does not implement " . Template::class . ' or it does not exist.');
		}

		$this->templateClass = $templateClass ?: DefaultTemplate::class;
	}


	public function createTemplate(?UI\Control $control = null, ?string $class = null): Template
	{
		$class ??= $this->templateClass;
		if (!is_a($class, Template::class, true)) {
			throw new Nette\InvalidArgumentException("Class $class does not implement " . Template::class . ' or it does not exist.');
		}

		$latte = $this->latteFactory->create();
		$template = new $class($latte);
		$presenter = $control ? $control->getPresenterIfExists() : null;

		$latte->addExtension(new UIExtension($control));

		if ($this->cacheStorage && class_exists(Nette\Bridges\CacheLatte\CacheExtension::class)) {
			$latte->addExtension(new Nette\Bridges\CacheLatte\CacheExtension($this->cacheStorage));
		}

		if (class_exists(Nette\Bridges\FormsLatte\FormsExtension::class)) {
			$latte->addExtension(new Nette\Bridges\FormsLatte\FormsExtension);
		}

		$latte->addFilter('modifyDate', fn($time, $delta, $unit = null) => $time
				? Nette\Utils\DateTime::from($time)->modify($delta . $unit)
				: null);

		if (!isset($latte->getFilters()['translate'])) {
			$latte->addFilter('translate', function (Latte\Runtime\FilterInfo $fi): void {
				throw new Nette\InvalidStateException('Translator has not been set. Set translator using $template->setTranslator().');
			});
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
			'config' => $control instanceof UI\Presenter && $this->configVars ? (object) $this->configVars : null,
		];

		foreach ($params as $key => $value) {
			if ($value !== null && property_exists($template, $key)) {
				$template->$key = $value;
			}
		}

		Nette\Utils\Arrays::invoke($this->onCreate, $template);

		return $template;
	}
}
