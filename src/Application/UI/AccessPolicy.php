<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\UI;

use Nette;
use Nette\Application\Attributes;
use Nette\Utils\Reflection;


/**
 * Manages access control to presenter elements based on attributes and built-in rules.
 * @internal
 */
final class AccessPolicy
{
	private Presenter $presenter;


	public function __construct(
		private readonly Component $component,
		private readonly \ReflectionClass|\ReflectionMethod $element,
	) {
	}


	public function checkAccess(): void
	{
		$attrs = $this->getAttributes();
		$attrs = self::applyInternalRules($attrs);
		foreach ($attrs as $attribute) {
			$this->checkAttribute($attribute);
		}
	}


	private function getAttributes(): array
	{
		return array_map(
			fn($ra) => $ra->newInstance(),
			$this->element->getAttributes(Attributes\Requires::class, \ReflectionAttribute::IS_INSTANCEOF),
		);
	}


	private function applyInternalRules(array $attrs): array
	{
		if (
			$this->element instanceof \ReflectionMethod
			&& str_starts_with($this->element->getName(), $this->component::formatSignalMethod(''))
			&& !ComponentReflection::parseAnnotation($this->element, 'crossOrigin')
			&& !Nette\Utils\Arrays::some($attrs, fn($attr) => $attr->sameOrigin === false)
		) {
			$attrs[] = new Attributes\Requires(sameOrigin: true);
		}
		return $attrs;
	}


	private function checkAttribute(Attributes\Requires $attribute): void
	{
		$this->presenter ??= $this->component->getPresenterIfExists() ??
			throw new Nette\InvalidStateException('Presenter is required for checking requirements of ' . Reflection::toString($this->element));

		if ($attribute->methods !== null) {
			$this->checkHttpMethod($attribute);
		}

		if ($attribute->actions !== null) {
			$this->checkActions($attribute);
		}

		if (
			$attribute->forward
			&& !$this->presenter->getRequest()->isMethod($this->presenter->getRequest()::FORWARD)
			&& $this->presenter->getAction() === $this->presenter->getView()
		) {
			$this->presenter->error('Forwarded request is required by ' . Reflection::toString($this->element));
		}

		if ($attribute->sameOrigin && !$this->presenter->getHttpRequest()->isSameSite()) {
			$this->presenter->detectedCsrf();
		}

		if ($attribute->ajax && !$this->presenter->getHttpRequest()->isAjax()) {
			$this->presenter->error('AJAX request is required by ' . Reflection::toString($this->element), Nette\Http\IResponse::S403_Forbidden);
		}
	}


	private function checkActions(Attributes\Requires $attribute): void
	{
		if (
			$this->element instanceof \ReflectionMethod
			&& !$this->element->getDeclaringClass()->isSubclassOf(Presenter::class)
		) {
			throw new \LogicException('Requires(actions) used by ' . Reflection::toString($this->element) . ' is allowed only in presenter.');
		}

		if (!in_array($this->presenter->getAction(), $attribute->actions, strict: true)) {
			$this->presenter->error("Action '{$this->presenter->getAction()}' is not allowed by " . Reflection::toString($this->element));
		}
	}


	private function checkHttpMethod(Attributes\Requires $attribute): void
	{
		if ($this->element instanceof \ReflectionClass) {
			$this->presenter->allowedMethods = []; // bypass Presenter::checkHttpMethod()
		}

		$allowed = array_map(strtoupper(...), $attribute->methods);
		$method = $this->presenter->getHttpRequest()->getMethod();

		if ($allowed !== ['*'] && !in_array($method, $allowed, strict: true)) {
			$this->presenter->getHttpResponse()->setHeader('Allow', implode(',', $allowed));
			$this->presenter->error(
				"Method $method is not allowed by " . Reflection::toString($this->element),
				Nette\Http\IResponse::S405_MethodNotAllowed,
			);
		}
	}
}
