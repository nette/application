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
	private static $ppCache = array();

	/** @var array getPersistentComponents cache */
	private static $pcCache = array();

	/** @var array isMethodCallable cache */
	private static $mcCache = array();


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
		$params = array();
		if (is_subclass_of($class, 'Nette\Application\UI\PresenterComponent')) {
			$defaults = get_class_vars($class);
			foreach ($class::getPersistentParams() as $name => $default) {
				if (is_int($name)) {
					$name = $default;
					$default = $defaults[$name];
				}
				$params[$name] = array(
					'def' => $default,
					'since' => $class,
				);
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
		$components = array();
		if (is_subclass_of($class, 'Nette\Application\UI\Presenter')) {
			foreach ($class::getPersistentComponents() as $name => $meta) {
				if (is_string($meta)) {
					$name = $meta;
				}
				$components[$name] = array('since' => $class);
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
		$res = array();
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
			} elseif ($type === 'array') {
				$res[$i] = array();
			} elseif ($type === 'NULL' || $isClass) {
				$res[$i] = NULL;
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
	public static function convertType(& $val, $type, $isClass = FALSE)
	{
		if ($type === 'callable') {
			return FALSE;

		} elseif ($type === 'NULL' || $isClass) { // means 'not array', ignore class type hint
			return !is_array($val);

		} elseif (is_object($val)) {
			// ignore

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
		$res = array();
		foreach ($m[1] as $s) {
			$arr = $s === '' ? array(TRUE) : preg_split('#\s*,\s*#', $s, -1, PREG_SPLIT_NO_EMPTY);
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
			return array((string) $param->getType() ?: $def, $param->hasType() && !$param->getType()->isBuiltin());
		} elseif ($param->isArray()) {
			return array('array', FALSE);
		} elseif (PHP_VERSION_ID >= 50400 && $param->isCallable()) {
			return array('callable', FALSE);
		} else {
			try {
				return ($ref = $param->getClass()) ? array($ref->getName(), TRUE) : array($def, FALSE);
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
