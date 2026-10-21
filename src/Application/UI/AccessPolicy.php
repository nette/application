<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

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
	/** @var array<class-string, Attributes\Requires[]> */
	private static array $classAttrsCache = [];
	private Presenter $presenter;


	public function __construct(
		/** @var \ReflectionClass<object>|\ReflectionMethod */
		private readonly \ReflectionClass|\ReflectionMethod $element,
	) {
	}


	public function checkAccess(Component $component): void
	{
		$attrs = self::applyInternalRules($this->getAttributes(), $component);
		if (!$attrs) {
			return;
		}

		$this->presenter ??= $component->getPresenter(throw: false)
			?? throw new Nette\InvalidStateException('Presenter is required for checking requirements of ' . Reflection::toString($this->element));

		foreach ($attrs as $attribute) {
			$this->checkAttribute($attribute);
		}
	}


	public function isLinkable(): bool
	{
		$attrs = $this->getAttributes();
		return !$attrs || !Nette\Utils\Arrays::some($attrs, fn($attr) => $attr->forward === true);
	}


	/**
	 * @return Attributes\Requires[]
	 */
	private function getAttributes(): array
	{
		return $this->element instanceof \ReflectionMethod
			? array_map(
				fn($ra) => $ra->newInstance(),
				$this->element->getAttributes(Attributes\Requires::class, \ReflectionAttribute::IS_INSTANCEOF),
			)
			: self::$classAttrsCache[$this->element->getName()] ??= $this->getClassAttributes($this->element);
	}


	/**
	 * Collects class-level attributes including those inherited from ancestor classes.
	 * A rule explicitly set in a descendant overrides the same rule of all its ancestors.
	 * @param  \ReflectionClass<object>  $class
	 * @return Attributes\Requires[]
	 */
	private function getClassAttributes(\ReflectionClass $class): array
	{
		$res = $overridden = [];
		for ($rc = $class; $rc; $rc = $rc->getParentClass()) {
			$defined = [];
			foreach ($rc->getAttributes(Attributes\Requires::class, \ReflectionAttribute::IS_INSTANCEOF) as $ra) {
				$attr = $ra->newInstance();
				$used = false;
				foreach (array_keys(array_filter(get_object_vars($attr), fn($value) => $value !== null)) as $prop) {
					if (isset($overridden[$prop])) {
						$attr->$prop = null;
					} else {
						$defined[$prop] = $used = true;
					}
				}

				if ($used) {
					$res[] = $attr;
				}
			}

			$overridden += $defined;
		}

		return $res;
	}


	/**
	 * @param  Attributes\Requires[]  $attrs
	 * @return Attributes\Requires[]
	 */
	private function applyInternalRules(array $attrs, Component $component): array
	{
		if (
			$this->element instanceof \ReflectionMethod
			&& str_starts_with($this->element->getName(), $component::formatSignalMethod(''))
			&& !Nette\Utils\Arrays::some($attrs, fn($attr) => $attr->sameOrigin === false)
		) {
			$attrs[] = new Attributes\Requires(sameOrigin: true);
		}
		return $attrs;
	}


	private function checkAttribute(Attributes\Requires $attribute): void
	{
		if ($attribute->methods !== null) {
			$this->checkHttpMethod($attribute->methods);
		}

		if ($attribute->actions !== null) {
			$this->checkActions($attribute->actions);
		}

		if ($attribute->forward && !$this->presenter->isForwarded()) {
			$this->presenter->error('Forwarded request is required by ' . Reflection::toString($this->element));
		}

		if ($attribute->sameOrigin && !$this->presenter->getHttpRequest()->isFrom(Nette\Http\FetchSite::SameOrigin)) {
			$this->presenter->detectedCsrf();
		}

		if ($attribute->ajax && !$this->presenter->getHttpRequest()->isAjax()) {
			$this->presenter->error(
				'AJAX request is required by ' . Reflection::toString($this->element),
				Nette\Http\IResponse::S403_Forbidden,
			);
		}
	}


	/** @param  list<string>  $actions */
	private function checkActions(array $actions): void
	{
		if (
			$this->element instanceof \ReflectionMethod
			&& !$this->element->getDeclaringClass()->isSubclassOf(Presenter::class)
		) {
			throw new \LogicException('Requires(actions) used by ' . Reflection::toString($this->element) . ' is allowed only in presenter.');
		}

		if (!in_array($this->presenter->getAction(), $actions, true)) {
			$this->presenter->error("Action '{$this->presenter->getAction()}' is not allowed by " . Reflection::toString($this->element));
		}
	}


	/** @param  list<string>  $methods */
	private function checkHttpMethod(array $methods): void
	{
		if ($this->element instanceof \ReflectionClass) {
			$this->presenter->allowedMethods = []; // dresscode:ignore upgrading.declarations.deprecatedMember -- bypass Presenter::checkHttpMethod()
		}

		$allowed = array_map(strtoupper(...), $methods);
		$method = $this->presenter->getHttpRequest()->getMethod();

		if ($allowed !== ['*'] && !in_array($method, $allowed, true)) {
			$this->presenter->getHttpResponse()->setHeader('Allow', implode(',', $allowed));
			$this->presenter->error(
				"Method $method is not allowed by " . Reflection::toString($this->element),
				Nette\Http\IResponse::S405_MethodNotAllowed,
			);
		}
	}
}
