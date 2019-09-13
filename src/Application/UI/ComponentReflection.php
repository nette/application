<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\UI;

use Nette;


/**
 * Helpers for Presenter & Component.
 * @property-read string $name
 * @property-read string $fileName
 * @internal
 */
final class ComponentReflection extends \ReflectionClass
{
	use Nette\SmartObject;

	/** @var array getPersistentParams cache */
	private static $ppCache = [];

	/** @var array getPersistentComponents cache */
	private static $pcCache = [];

	/** @var array isMethodCallable cache */
	private static $mcCache = [];


	/**
	 * Returns array of classes persistent parameters. They have public visibility,
	 * are non-static and have annotation @persistent.
	 */
	public function getPersistentParams(string $class = null): array
	{
		$class = $class === null ? $this->getName() : $class;
		$params = &self::$ppCache[$class];
		if ($params !== null) {
			return $params;
		}
		$params = [];
		if (is_subclass_of($class, Component::class)) {
			$isPresenter = is_subclass_of($class, Presenter::class);
			$defaults = get_class_vars($class);
			foreach ($defaults as $name => $default) {
				$rp = new \ReflectionProperty($class, $name);
				if (!$rp->isStatic() && self::parseAnnotation($rp, 'persistent')) {
					$params[$name] = [
						'def' => $default,
						'type' => Nette\Utils\Reflection::getPropertyType($rp) ?: gettype($default),
						'since' => $isPresenter ? Nette\Utils\Reflection::getPropertyDeclaringClass($rp)->getName() : null,
					];
				}
			}
			foreach ($this->getPersistentParams(get_parent_class($class)) as $name => $param) {
				if (isset($params[$name])) {
					$params[$name]['since'] = $param['since'];
				} else {
					$params[$name] = $param;
				}
			}
		}
		return $params;
	}


	public function getPersistentComponents(string $class = null): array
	{
		$class = $class === null ? $this->getName() : $class;
		$components = &self::$pcCache[$class];
		if ($components !== null) {
			return $components;
		}
		$components = [];
		if (is_subclass_of($class, Presenter::class)) {
			foreach ($class::getPersistentComponents() as $name => $meta) {
				if (is_string($meta)) {
					$name = $meta;
				}
				$components[$name] = ['since' => $class];
			}
			$components = $this->getPersistentComponents(get_parent_class($class)) + $components;
		}
		return $components;
	}


	/**
	 * Saves state informations for next request.
	 */
	public function saveState(Component $component, array &$params): void
	{
		$tree = self::getClassesAndTraits(get_class($component));

		foreach ($this->getPersistentParams() as $name => $meta) {
			if (isset($params[$name])) {
				// injected value

			} elseif (
				array_key_exists($name, $params) // nulls are skipped
				|| (isset($meta['since']) && !isset($tree[$meta['since']])) // not related
				|| !isset($component->$name)
			) {
				continue;

			} else {
				$params[$name] = $component->$name; // object property value
			}

			if (!self::convertType($params[$name], $meta['type'])) {
				throw new InvalidLinkException(sprintf(
					"Value passed to persistent parameter '%s' in %s must be %s, %s given.",
					$name,
					$component instanceof Presenter ? 'presenter ' . $component->getName() : "component '{$component->getUniqueId()}'",
					$meta['type'] === 'NULL' ? 'scalar' : $meta['type'],
					is_object($params[$name]) ? get_class($params[$name]) : gettype($params[$name])
				));
			}

			if ($params[$name] === $meta['def'] || ($meta['def'] === null && $params[$name] === '')) {
				$params[$name] = null; // value transmit is unnecessary
			}
		}
	}


	/**
	 * Is a method callable? It means class is instantiable and method has
	 * public visibility, is non-static and non-abstract.
	 */
	public function hasCallableMethod(string $method): bool
	{
		$class = $this->getName();
		$cache = &self::$mcCache[strtolower($class . ':' . $method)];
		if ($cache === null) {
			try {
				$cache = false;
				$rm = new \ReflectionMethod($class, $method);
				$cache = $this->isInstantiable() && $rm->isPublic() && !$rm->isAbstract() && !$rm->isStatic();
			} catch (\ReflectionException $e) {
			}
		}
		return $cache;
	}


	public static function combineArgs(\ReflectionFunctionAbstract $method, array $args): array
	{
		$res = [];
		foreach ($method->getParameters() as $i => $param) {
			$name = $param->getName();
			[$type, $isClass] = self::getParameterType($param);
			if (isset($args[$name])) {
				$res[$i] = $args[$name];
				if (!self::convertType($res[$i], $type, $isClass)) {
					throw new Nette\InvalidArgumentException(sprintf(
						'Argument $%s passed to %s() must be %s, %s given.',
						$name,
						($method instanceof \ReflectionMethod ? $method->getDeclaringClass()->getName() . '::' : '') . $method->getName(),
						$type === 'NULL' ? 'scalar' : $type,
						is_object($args[$name]) ? get_class($args[$name]) : gettype($args[$name])
					));
				}
			} elseif ($param->isDefaultValueAvailable()) {
				$res[$i] = $param->getDefaultValue();
			} elseif ($type === 'NULL' || $param->allowsNull()) {
				$res[$i] = null;
			} elseif ($type === 'array' || $type === 'iterable') {
				$res[$i] = [];
			} else {
				throw new Nette\InvalidArgumentException(sprintf(
					'Missing parameter $%s required by %s()',
					$name,
					($method instanceof \ReflectionMethod ? $method->getDeclaringClass()->getName() . '::' : '') . $method->getName()
				));
			}
		}
		return $res;
	}


	/**
	 * Non data-loss type conversion.
	 */
	public static function convertType(&$val, string $type, bool $isClass = false): bool
	{
		if ($isClass) {
			return $val instanceof $type;

		} elseif ($type === 'callable') {
			return false;

		} elseif ($type === 'NULL') { // means 'not array'
			return !is_array($val);

		} elseif ($type === 'array' || $type === 'iterable') {
			return is_array($val);

		} elseif (!is_scalar($val)) { // array, resource, null, etc.
			return false;

		} else {
			$tmp = ($val === false ? '0' : (string) $val);
			if ($type === 'double' || $type === 'float') {
				$tmp = preg_replace('#\.0*$#D', '', $tmp);
			}
			$orig = $tmp;
			settype($tmp, $type);
			if ($orig !== ($tmp === false ? '0' : (string) $tmp)) {
				return false; // data-loss occurs
			}
			$val = $tmp;
		}
		return true;
	}


	/**
	 * Returns an annotation value.
	 * @param  \ReflectionClass|\ReflectionMethod  $ref
	 */
	public static function parseAnnotation(\Reflector $ref, string $name): ?array
	{
		if (!preg_match_all('#[\s*]@' . preg_quote($name, '#') . '(?:\(\s*([^)]*)\s*\)|\s|$)#', (string) $ref->getDocComment(), $m)) {
			return null;
		}
		static $tokens = ['true' => true, 'false' => false, 'null' => null];
		$res = [];
		foreach ($m[1] as $s) {
			foreach (preg_split('#\s*,\s*#', $s, -1, PREG_SPLIT_NO_EMPTY) ?: ['true'] as $item) {
				$res[] = array_key_exists($tmp = strtolower($item), $tokens) ? $tokens[$tmp] : $item;
			}
		}
		return $res;
	}


	/**
	 * @return array [string|null, bool]
	 */
	public static function getParameterType(\ReflectionParameter $param): array
	{
		return $param->hasType()
			? [$param->getType()->getName(), !$param->getType()->isBuiltin()]
			: [gettype($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null), false];
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
	 * @return mixed
	 */
	public function getAnnotation(string $name)
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


	/**
	 * return string[]
	 */
	public static function getClassesAndTraits(string $class): array
	{
		$res = [$class => $class] + class_parents($class);
		$addTraits = function (string $type) use (&$res, &$addTraits): void {
			$res += class_uses($type);
			foreach (class_uses($type) as $trait) {
				$addTraits($trait);
			}
		};
		foreach ($res as $type) {
			$addTraits($type);
		}
		return $res;
	}
}
