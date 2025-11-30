<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte;

use Nette;
use Nette\Application\UI;
use function class_exists, is_a, preg_replace, property_exists, rtrim;


/**
 * Creates a class of parameters of Latte templates.
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
		$templateClass = null,
		private bool $generate = false,
	) {
		if ($templateClass && (!class_exists($templateClass) || !is_a($templateClass, Template::class, true))) {
			throw new Nette\InvalidArgumentException("Class $templateClass does not implement " . Template::class . ' or it does not exist.');
		}

		$this->templateClass = $templateClass ?? DefaultTemplate::class;
	}


	/** @return Template */
	public function createTemplate(?UI\Control $control = null, ?string $class = null): UI\Template
	{
		$class ??= $this->templateClass;
		if (!is_a($class, Template::class, allow_string: true)) {
			throw new Nette\InvalidArgumentException("Class $class does not implement " . Template::class . ' or it does not exist.');
		}

		$latte = $this->latteFactory->create($control);
		$template = $this->generate && $control instanceof UI\Presenter
			? new TemplateGenerator($latte, $class, $control)
			: new $class($latte);
		$this->injectDefaultVariables($template, $control);

		Nette\Utils\Arrays::invoke($this->onCreate, $template);

		return $template;
	}


	private function injectDefaultVariables(Template $template, ?UI\Control $control): void
	{
		$presenter = $control?->getPresenterIfExists();
		$baseUrl = $this->httpRequest
			? rtrim($this->httpRequest->getUrl()->withoutUserInfo()->getBaseUrl(), '/')
			: null;
		$flashes = $presenter instanceof UI\Presenter && $presenter->hasFlashSession()
			? (array) $presenter->getFlashSession()->get($control->getParameterId('flash'))
			: [];

		$vars = [
			'user' => $this->user,
			'baseUrl' => $baseUrl,
			'basePath' => $baseUrl ? preg_replace('#https?://[^/]+#A', '', $baseUrl) : null,
			'flashes' => $flashes,
			'control' => $control,
			'presenter' => $presenter,
		];

		foreach ($vars as $key => $value) {
			if ($value !== null && property_exists($template, $key)) {
				try {
					$template->$key = $value;
				} catch (\TypeError) {
				}
			} elseif ($template instanceof TemplateGenerator) {
				$template->addDefaultVariable($key, $value);
			}
		}
	}
}
