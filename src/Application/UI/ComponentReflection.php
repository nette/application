<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\UI;

use Nette;
use Nette\Application\BadRequestException;
use Nette\Reflection\ClassType;


/**
 * Helpers for Presenter & Component.
 * @property-read string $name
 * @property-read string $fileName
 * @internal
 */
class ComponentReflection extends \ReflectionClass
{
	use Nette\SmartObject;

	/** @var array getPersistentParams cache */
	private static $ppCache = [];

	/** @var array getPersistentComponents cache */
	private static $pcCache = [];

	/** @var array isMethodCallable cache */
	private static $mcCache = [];


	/**
	 * @param  string|null
	 * @return array of persistent parameters.
	 */
	public function getPersistentParams($class = null)
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
			foreach ($class::getPersistentParams() as $name => $default) {
				if (is_int($name)) {
					$name = $default;
					$default = $defaults[$name];
				}
				$params[$name] = [
					'def' => $default,
					'since' => $isPresenter ? $class : null,
				];
			}
			foreach ($this->getPersistentParams(get_parent_class($class)) as $name => $param) {
				if (isset($params[$name])) {
					$params[$name]['since'] = $param['since'];
					continue;
				}

				$params[$name] = $param;
			}
		}
		return $params;
	}


	/**
	 * @param  string|null
	 * @return array of persistent components.
	 */
	public function getPersistentComponents($class = null)
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
	public function saveState(Component $component, array &$params)
	{
		foreach ($this->getPersistentParams() as $name => $meta) {
			if (isset($params[$name])) {
				// injected value

			} elseif (
				array_key_exists($name, $params) // nulls are skipped
				|| (isset($meta['since']) && !$component instanceof $meta['since']) // not related
				|| !isset($component->$name)
			) {
				continue;

			} else {
				$params[$name] = $component->$name; // object property value
			}

			$type = gettype($meta['def']);
			if (!self::convertType($params[$name], $type)) {
				throw new InvalidLinkException(sprintf(
					"Value passed to persistent parameter '%s' in %s must be %s, %s given.",
					$name,
					$component instanceof Presenter ? 'presenter ' . $component->getName() : "component '{$component->getUniqueId()}'",
					$type === 'NULL' ? 'scalar' : $type,
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
	 * @param  string  method name
	 * @return bool
	 */
	public function hasCallableMethod($method)
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


	/**
	 * @return array
	 */
	public static function combineArgs(\ReflectionFunctionAbstract $method, $args)
	{
		$res = [];
		foreach ($method->getParameters() as $i => $param) {
			$name = $param->getName();
			list($type, $isClass) = self::getParameterType($param);
			if (isset($args[$name])) {
				$res[$i] = $args[$name];
				if (!self::convertType($res[$i], $type, $isClass)) {
					throw new BadRequestException(sprintf(
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
				throw new BadRequestException(sprintf(
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
	 * @param  mixed
	 * @param  string
	 * @return bool
	 */
	public static function convertType(&$val, $type, $isClass = false)
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
				$tmp = preg_replace('#\.0*\z#', '', $tmp);
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
	 * @return array|false
	 */
	public static function parseAnnotation(\Reflector $ref, $name)
	{
		if (!preg_match_all('#[\\s*]@' . preg_quote($name, '#') . '(?:\(\\s*([^)]*)\\s*\)|\\s|$)#', $ref->getDocComment(), $m)) {
			return false;
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
	public static function getParameterType(\ReflectionParameter $param)
	{
		$def = gettype($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null);
		if (PHP_VERSION_ID >= 70000) {
			return $param->hasType()
				? [PHP_VERSION_ID >= 70100 ? $param->getType()->getName() : (string) $param->getType(), !$param->getType()->isBuiltin()]
				: [$def, false];
		} elseif ($param->isArray() || $param->isCallable()) {
			return [$param->isArray() ? 'array' : 'callable', false];
		} else {
			try {
				return ($ref = $param->getClass()) ? [$ref->getName(), true] : [$def, false];
			} catch (\ReflectionException $e) {
				if (preg_match('#Class (.+) does not exist#', $e->getMessage(), $m)) {
					throw new \LogicException(sprintf(
						"Class %s not found. Check type hint of parameter $%s in %s() or 'use' statements.",
						$m[1],
						$param->getName(),
						$param->getDeclaringFunction()->getDeclaringClass()->getName() . '::' . $param->getDeclaringFunction()->getName()
					));
				}
				throw $e;
			}
		}
	}


	/********************* compatiblity with Nette\Reflection ****************d*g**/


	/**
	 * Has class specified annotation?
	 * @param  string
	 * @return bool
	 */
	public function hasAnnotation($name)
	{
		return (bool) self::parseAnnotation($this, $name);
	}


	/**
	 * Returns an annotation value.
	 * @param  string
	 * @return mixed
	 */
	public function getAnnotation($name)
	{
		$res = self::parseAnnotation($this, $name);
		return $res ? end($res) : null;
	}


	/**
	 * @return MethodReflection
	 */
	public function getMethod($name)
	{
		return new MethodReflection($this->getName(), $name);
	}


	/**
	 * @return MethodReflection[]
	 */
	public function getMethods($filter = -1)
	{
		foreach ($res = parent::getMethods($filter) as $key => $val) {
			$res[$key] = new MethodReflection($this->getName(), $val->getName());
		}
		return $res;
	}


	public function __toString()
	{
		trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
		return $this->getName();
	}


	public function __get($name)
	{
		trigger_error("getReflection()->$name is deprecated.", E_USER_DEPRECATED);
		return (new ClassType($this->getName()))->$name;
	}


	public function __call($name, $args)
	{
		if (method_exists(ClassType::class, $name)) {
			trigger_error("getReflection()->$name() is deprecated, use Nette\\Reflection\\ClassType::from(\$presenter)->$name()", E_USER_DEPRECATED);
			return call_user_func_array([new ClassType($this->getName()), $name], $args);
		}
		Nette\Utils\ObjectMixin::strictCall(get_class($this), $name);
	}
}
