<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\UI;

use Nette;
use Nette\Application\BadRequestException;


/**
 * Helpers for Presenter & PresenterComponent.
 * @internal
 */
class PresenterComponentReflection extends Nette\Reflection\ClassType
{
	/** @var array getPersistentParams cache */
	private static $ppCache = [];

	/** @var array getPersistentComponents cache */
	private static $pcCache = [];

	/** @var array isMethodCallable cache */
	private static $mcCache = [];


	/**
	 * @param  string|NULL
	 * @return array of persistent parameters.
	 */
	public function getPersistentParams($class = NULL)
	{
		$class = $class === NULL ? $this->getName() : $class;
		$params = & self::$ppCache[$class];
		if ($params !== NULL) {
			return $params;
		}
		$params = [];
		if (is_subclass_of($class, PresenterComponent::class)) {
			$defaults = get_class_vars($class);
			foreach ($class::getPersistentParams() as $name => $default) {
				if (is_int($name)) {
					$name = $default;
					$default = $defaults[$name];
				}
				$params[$name] = [
					'def' => $default,
					'since' => $class,
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
	 * @param  string|NULL
	 * @return array of persistent components.
	 */
	public function getPersistentComponents($class = NULL)
	{
		$class = $class === NULL ? $this->getName() : $class;
		$components = & self::$pcCache[$class];
		if ($components !== NULL) {
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
	 * Is a method callable? It means class is instantiable and method has
	 * public visibility, is non-static and non-abstract.
	 * @param  string  method name
	 * @return bool
	 */
	public function hasCallableMethod($method)
	{
		$class = $this->getName();
		$cache = & self::$mcCache[strtolower($class . ':' . $method)];
		if ($cache === NULL) {
			try {
				$cache = FALSE;
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
		$i = 0;
		foreach ($method->getParameters() as $param) {
			$name = $param->getName();
			if (!isset($args[$name]) && $param->isDefaultValueAvailable()) {
				$res[$i++] = $param->getDefaultValue();
			} else {
				$res[$i++] = $arg = isset($args[$name]) ? $args[$name] : NULL;
				list($type, $isClass) = self::getParameterType($param);
				if (!self::convertType($arg, $type, $isClass)) {
					throw new BadRequestException(sprintf(
						'Argument $%s passed to %s() must be %s, %s given.',
						$name,
						($method instanceof \ReflectionMethod ? $method->getDeclaringClass()->getName() . '::' : '') . $method->getName(),
						$type === 'NULL' ? 'scalar' : $type,
						is_object($arg) ? get_class($arg) : gettype($arg)
					));
				}
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
	public static function convertType(& $val, $type, $isClass = FALSE)
	{
		if ($isClass) {
			return $val instanceof $type;

		} elseif ($type === 'callable') {
			return FALSE;

		} elseif ($type === 'NULL') { // means 'not array'
			return !is_array($val);

		} elseif ($val === NULL) {
			settype($val, $type); // to scalar or array

		} elseif ($type === 'array') {
			return is_array($val);

		} elseif (!is_scalar($val)) { // array, resource, etc.
			return FALSE;

		} else {
			$old = $tmp = ($val === FALSE ? '0' : (string) $val);
			settype($tmp, $type);
			if ($old !== ($tmp === FALSE ? '0' : (string) $tmp)) {
				return FALSE; // data-loss occurs
			}
			$val = $tmp;
		}
		return TRUE;
	}


	/**
	 * Returns an annotation value.
	 * @return array|FALSE
	 */
	public static function parseAnnotation(\Reflector $ref, $name)
	{
		if (!preg_match_all("#[\\s*]@$name(?:\(\\s*([^)]*)\\s*\))?#", $ref->getDocComment(), $m)) {
			return FALSE;
		}
		$res = [];
		foreach ($m[1] as $s) {
			$arr = $s === '' ? [TRUE] : preg_split('#\s*,\s*#', $s, -1, PREG_SPLIT_NO_EMPTY);
			$res = array_merge($res, $arr);
		}
		return $res;
	}


	/**
	 * @return [string, bool]
	 */
	public static function getParameterType(\ReflectionParameter $param)
	{
		$def = gettype($param->isDefaultValueAvailable() ? $param->getDefaultValue() : NULL);
		if (PHP_VERSION_ID >= 70000) {
			return [(string) $param->getType() ?: $def, $param->hasType() && !$param->getType()->isBuiltin()];
		} elseif ($param->isArray() || $param->isCallable()) {
			return [$param->isArray() ? 'array' : 'callable', FALSE];
		} else {
			try {
				return ($ref = $param->getClass()) ? [$ref->getName(), TRUE] : [$def, FALSE];
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

}
