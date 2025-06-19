<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\UI;

use Nette\Application\Attributes;
use Nette\Utils\Reflection;
use function array_fill_keys, array_filter, array_key_exists, array_merge, class_exists, end, preg_match_all, preg_quote, preg_split, strtolower;
use const PREG_SPLIT_NO_EMPTY;


/**
 * Helpers for Presenter & Component.
 * @property-read string $name
 * @property-read string $fileName
 * @internal
 */
final class ComponentReflection extends \ReflectionClass
{
	private static array $ppCache = [];
	private static array $pcCache = [];
	private static array $armCache = [];


	/**
	 * Returns array of class properties that are public and have attribute #[Persistent] or #[Parameter] or annotation @persistent.
	 * @return array<string, array{def: mixed, type: string, since: ?string}>
	 */
	public function getParameters(): array
	{
		$params = &self::$ppCache[$this->getName()];
		if ($params !== null) {
			return $params;
		}

		$params = [];
		$isPresenter = $this->isSubclassOf(Presenter::class);
		foreach ($this->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
			if ($prop->isStatic()) {
				continue;
			} elseif (
				self::parseAnnotation($prop, 'persistent')
				|| $prop->getAttributes(Attributes\Persistent::class)
			) {
				$params[$prop->getName()] = [
					'def' => $prop->getDefaultValue(),
					'type' => ParameterConverter::getType($prop),
					'since' => $isPresenter ? Reflection::getPropertyDeclaringClass($prop)->getName() : null,
				];
			} elseif ($prop->getAttributes(Attributes\Parameter::class)) {
				$params[$prop->getName()] = [
					'type' => (string) ($prop->getType() ?? 'mixed'),
				];
			}
		}

		if ($this->getParentClass()->isSubclassOf(Component::class)) {
			$parent = new self($this->getParentClass()->getName());
			foreach ($parent->getParameters() as $name => $meta) {
				if (!isset($params[$name])) {
					$params[$name] = $meta;
				} elseif (array_key_exists('since', $params[$name])) {
					$params[$name]['since'] = $meta['since'];
				}
			}
		}

		return $params;
	}


	/**
	 * Returns array of persistent properties. They are public and have attribute #[Persistent] or annotation @persistent.
	 * @return array<string, array{def: mixed, type: string, since: string}>
	 */
	public function getPersistentParams(): array
	{
		return array_filter($this->getParameters(), fn($param) => array_key_exists('since', $param));
	}


	/**
	 * Returns array of persistent components. They are tagged with class-level attribute
	 * #[Persistent] or annotation @persistent or returned by Presenter::getPersistentComponents().
	 * @return array<string, array{since: string}>
	 */
	public function getPersistentComponents(): array
	{
		$class = $this->getName();
		$components = &self::$pcCache[$class];
		if ($components !== null) {
			return $components;
		}

		$attrs = $this->getAttributes(Attributes\Persistent::class);
		$names = $attrs
			? $attrs[0]->getArguments()
			: (array) self::parseAnnotation($this, 'persistent');
		$names = array_merge($names, $class::getPersistentComponents());
		$components = array_fill_keys($names, ['since' => $class]);

		if ($this->isSubclassOf(Presenter::class)) {
			$parent = new self($this->getParentClass()->getName());
			$components = $parent->getPersistentComponents() + $components;
		}

		return $components;
	}


	/**
	 * Is a method callable? It means class is instantiable and method has
	 * public visibility, is non-static and non-abstract.
	 */
	public function hasCallableMethod(string $method): bool
	{
		return $this->isInstantiable()
			&& $this->hasMethod($method)
			&& ($rm = $this->getMethod($method))
			&& $rm->isPublic() && !$rm->isAbstract() && !$rm->isStatic();
	}


	/** Returns action*() or render*() method if available */
	public function getActionRenderMethod(string $action): ?\ReflectionMethod
	{
		$class = $this->name;
		return self::$armCache[$class][$action] ??=
			$this->hasCallableMethod($name = $class::formatActionMethod($action))
			|| $this->hasCallableMethod($name = $class::formatRenderMethod($action))
				? parent::getMethod($name)
				: null;
	}


	/** Returns handle*() method if available */
	public function getSignalMethod(string $signal): ?\ReflectionMethod
	{
		$class = $this->name;
		return $this->hasCallableMethod($name = $class::formatSignalMethod($signal))
			? parent::getMethod($name)
			: null;
	}


	/**
	 * Returns an annotation value.
	 */
	public static function parseAnnotation(\Reflector $ref, string $name): ?array
	{
		if (!preg_match_all('#[\s*]@' . preg_quote($name, '#') . '(?:\(\s*([^)]*)\s*\)|\s|$)#', (string) $ref->getDocComment(), $m)) {
			return null;
		}

		$tokens = ['true' => true, 'false' => false, 'null' => null];
		$res = [];
		foreach ($m[1] as $s) {
			foreach (preg_split('#\s*,\s*#', $s, -1, PREG_SPLIT_NO_EMPTY) ?: ['true'] as $item) {
				$res[] = array_key_exists($tmp = strtolower($item), $tokens)
					? $tokens[$tmp]
					: $item;
			}
		}

		return $res;
	}


	/**
	 * Has class specified annotation?
	 */
	public function hasAnnotation(string $name): bool
	{
		return (bool) self::parseAnnotation($this, $name);
	}


	/**
	 * Returns an annotation value.
	 */
	public function getAnnotation(string $name): mixed
	{
		$res = self::parseAnnotation($this, $name);
		return $res ? end($res) : null;
	}


	public function getMethod($name): MethodReflection
	{
		return new MethodReflection($this->getName(), $name);
	}


	/**
	 * @return MethodReflection[]
	 */
	public function getMethods($filter = -1): array
	{
		foreach ($res = parent::getMethods($filter) as $key => $val) {
			$res[$key] = new MethodReflection($this->getName(), $val->getName());
		}

		return $res;
	}


	/** @deprecated  */
	public static function combineArgs(\ReflectionFunctionAbstract $method, array $args): array
	{
		return ParameterConverter::toArguments($method, $args);
	}
}


class_exists(PresenterComponentReflection::class);
