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

	private static array $ppCache = [];
	private static array $pcCache = [];
	private static array $mcCache = [];


	/**
	 * Returns array of class properties that are public and have attribute #[Persistent] or #[Parameter] or annotation @persistent.
	 */
	public function getParameters(): array
	{
		$params = &self::$ppCache[$this->getName()];
		if ($params !== null) {
			return $params;
		}

		$params = [];
		$isPresenter = $this->isSubclassOf(Presenter::class);
		$defaults = $this->getDefaultProperties();
		foreach ($this->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
			if ($prop->isStatic()) {
				continue;
			} elseif (
				self::parseAnnotation($prop, 'persistent')
				|| $prop->getAttributes(Nette\Application\Attributes\Persistent::class)
			) {
				$default = $defaults[$prop->getName()] ?? null;
				$params[$prop->getName()] = [
					'def' => $default,
					'type' => self::getPropertyType($prop, $default),
					'since' => $isPresenter ? Nette\Utils\Reflection::getPropertyDeclaringClass($prop)->getName() : null,
				];
			} elseif ($prop->getAttributes(Nette\Application\Attributes\Parameter::class)) {
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
	 */
	public function getPersistentParams(): array
	{
		return array_filter($this->getParameters(), fn($param) => array_key_exists('since', $param));
	}


	public function getPersistentComponents(): array
	{
		$class = $this->getName();
		$components = &self::$pcCache[$class];
		if ($components !== null) {
			return $components;
		}

		$components = [];
		if ($this->isSubclassOf(Presenter::class)) {
			foreach ($class::getPersistentComponents() as $name => $meta) {
				$components[is_string($meta) ? $meta : $name] = ['since' => $class];
			}

			$parent = new self($this->getParentClass()->getName());
			$components = $parent->getPersistentComponents() + $components;
		}

		return $components;
	}


	/**
	 * Saves state information for next request.
	 */
	public function saveState(Component $component, array &$params): void
	{
		$tree = self::getClassesAndTraits($component::class);

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
					$meta['type'],
					is_object($params[$name]) ? get_class($params[$name]) : gettype($params[$name]),
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
			} catch (\ReflectionException) {
			}
		}

		return $cache;
	}


	public static function combineArgs(\ReflectionFunctionAbstract $method, array $args): array
	{
		$res = [];
		foreach ($method->getParameters() as $i => $param) {
			$name = $param->getName();
			$type = self::getParameterType($param);
			if (isset($args[$name])) {
				$res[$i] = $args[$name];
				if (!self::convertType($res[$i], $type)) {
					throw new Nette\InvalidArgumentException(sprintf(
						'Argument $%s passed to %s() must be %s, %s given.',
						$name,
						($method instanceof \ReflectionMethod ? $method->getDeclaringClass()->getName() . '::' : '') . $method->getName(),
						$type,
						is_object($args[$name]) ? get_class($args[$name]) : gettype($args[$name]),
					));
				}
			} elseif ($param->isDefaultValueAvailable()) {
				$res[$i] = $param->getDefaultValue();
			} elseif ($type === 'scalar' || $param->allowsNull()) {
				$res[$i] = null;
			} elseif ($type === 'array' || $type === 'iterable') {
				$res[$i] = [];
			} else {
				throw new Nette\InvalidArgumentException(sprintf(
					'Missing parameter $%s required by %s()',
					$name,
					($method instanceof \ReflectionMethod ? $method->getDeclaringClass()->getName() . '::' : '') . $method->getName(),
				));
			}
		}

		return $res;
	}


	/**
	 * Lossless type conversion.
	 */
	public static function convertType(mixed &$val, string $types): bool
	{
		$scalars = ['string' => 1, 'int' => 1, 'float' => 1, 'bool' => 1, 'true' => 1, 'false' => 1, 'boolean' => 1, 'double' => 1, 'integer' => 1];
		$testable = ['iterable' => 1, 'object' => 1, 'array' => 1, 'null' => 1];

		foreach (explode('|', ltrim($types, '?')) as $type) {
			if (isset($scalars[$type])) {
				$ok = self::castScalar($val, $type);
			} elseif (isset($testable[$type])) {
				$ok = "is_$type"($val);
			} elseif ($type === 'scalar') { // special type due to historical reasons
				$ok = !is_array($val);
			} elseif ($type === 'mixed') {
				$ok = true;
			} elseif ($type === 'callable') { // intentionally disabled for security reasons
				$ok = false;
			} else {
				$ok = $val instanceof $type;
			}
			if ($ok) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Lossless type casting.
	 */
	private static function castScalar(mixed &$val, string $type): bool
	{
		if (!is_scalar($val)) {
			return false;
		}

		$tmp = ($val === false ? '0' : (string) $val);
		if ($type === 'double' || $type === 'float') {
			$tmp = preg_replace('#\.0*$#D', '', $tmp);
		}

		$orig = $tmp;
		$spec = ['true' => true, 'false' => false];
		isset($spec[$type]) ? $tmp = $spec[$type] : settype($tmp, $type);
		if ($orig !== ($tmp === false ? '0' : (string) $tmp)) {
			return false; // data-loss occurs
		}

		$val = $tmp;
		return true;
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


	public static function getParameterType(\ReflectionParameter $param): string
	{
		$default = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
		$type = $param->getType();
		return $type
			? ($type instanceof \ReflectionNamedType ? $type->getName() : (string) $type)
			: ($default === null ? 'scalar' : gettype($default));
	}


	public static function getPropertyType(\ReflectionProperty $prop, mixed $default): string
	{
		$type = $prop->getType();
		return $type
			? ($type instanceof \ReflectionNamedType ? $type->getName() : (string) $type)
			: ($default === null ? 'scalar' : gettype($default));
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


class_exists(PresenterComponentReflection::class);
