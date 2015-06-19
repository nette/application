<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application\UI;

use Nette;
use Nette\Application\BadRequestException;


/**
 * Helpers for Presenter & PresenterComponent.
 *
 * @author     David Grudl
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
		if (is_subclass_of($class, 'Nette\Application\UI\PresenterComponent')) {
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
		if (is_subclass_of($class, 'Nette\Application\UI\Presenter')) {
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
			if (isset($args[$name])) { // NULLs are ignored
				$res[$i++] = $args[$name];
				$type = $param->isArray() ? 'array' : ($param->isDefaultValueAvailable() ? gettype($param->getDefaultValue()) : 'NULL');
				if (!self::convertType($res[$i - 1], $type)) {
					$mName = $method instanceof \ReflectionMethod ? $method->getDeclaringClass()->getName() . '::' . $method->getName() : $method->getName();
					throw new BadRequestException("Invalid value for parameter '$name' in method $mName(), expected " . ($type === 'NULL' ? 'scalar' : $type) . ".");
				}
			} else {
				$res[$i++] = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : ($param->isArray() ? [] : NULL);
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
	public static function convertType(& $val, $type)
	{
		if ($val === NULL || is_object($val)) {
			// ignore
		} elseif ($type === 'array') {
			if (!is_array($val)) {
				return FALSE;
			}
		} elseif (!is_scalar($val)) {
			return FALSE;

		} elseif ($type !== 'NULL') {
			$old = $val = ($val === FALSE ? '0' : (string) $val);
			settype($val, $type);
			if ($old !== ($val === FALSE ? '0' : (string) $val)) {
				return FALSE; // data-loss occurs
			}
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

}
