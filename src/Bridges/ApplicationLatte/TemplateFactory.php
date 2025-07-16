<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte;

use Nette;
use Nette\Application\UI;


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
		$templateClass = null,
	) {
		if ($templateClass && (!class_exists($templateClass) || !is_a($templateClass, Template::class, true))) {
			throw new Nette\InvalidArgumentException("Class $templateClass does not implement " . Template::class . ' or it does not exist.');
		}

		$this->templateClass = $templateClass ?: DefaultTemplate::class;
	}


	public function createTemplate(?UI\Control $control = null, ?string $class = null): Template
	{
		$class ??= $this->templateClass;
		if (!is_a($class, Template::class, allow_string: true)) {
			throw new Nette\InvalidArgumentException("Class $class does not implement " . Template::class . ' or it does not exist.');
		}

		$latte = $this->latteFactory->create($control);
		$template = new $class($latte);
		$presenter = $control?->getPresenterIfExists();

		// default parameters
        $url = $this->httpRequest?->getUrl()->withoutUserInfo();
		$flashes = $presenter instanceof UI\Presenter && $presenter->hasFlashSession()
			? (array) $presenter->getFlashSession()->get($control->getParameterId('flash'))
			: [];

		$params = [
			'user' => $this->user,
            'baseUrl' => $url?->getBaseUrl(),
            'basePath' => $url?->getBasePath(),
			'flashes' => $flashes,
			'control' => $control,
			'presenter' => $presenter,
		];

        $checkTypes = !is_a($template, DefaultTemplate::class);
        if($checkTypes){
            $properties = [];
            $rc = new \ReflectionClass($template);
            foreach($rc->getProperties(\ReflectionProperty::IS_PUBLIC) as $property){
                $properties[$property->name] = $property;
            }
            foreach ($params as $key => $value) {
                if ($value !== null && isset($properties[$key])) {
                    $property = $properties[$key];
                    $type = $property->getType();
                    if($type instanceof \ReflectionNamedType){
                        $expected = $type->getName();
                        $isValid = $type->isBuiltin() ? match($expected){
                            'string' => is_string($value),
                            'int'    => is_int($value),
                            'bool'   => is_bool($value),
                            'array'  => is_array($value),
                            'float'  => is_float($value),
                            'object' => is_object($value),
                            default  => false,
                        }
                        : is_a($value, $expected);
                        if($isValid){
                            $template->$key = $value;
                        }
                    }
                }
            }

        }else{
            foreach ($params as $key => $value) {
                if ($value !== null && property_exists($template, $key)) {
                    $template->$key = $value;
                }
            }
        }

		Nette\Utils\Arrays::invoke($this->onCreate, $template);

		return $template;
	}
}
