<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\UI;

use Nette;
use Nette\Utils\Reflection;
use function array_key_exists, explode, get_debug_type, is_array, is_scalar, ltrim, preg_replace, settype, sprintf;


/**
 * Converts parameters between HTTP requests, presenters, and URLs.
 * @internal
 */
final class ParameterConverter
{
	use Nette\StaticClass;

	public static function toArguments(\ReflectionFunctionAbstract $method, array $args): array
	{
		$res = [];
		foreach ($method->getParameters() as $i => $param) {
			$name = $param->getName();
			$type = self::getType($param);
			if (isset($args[$name])) {
				$res[$i] = $args[$name];
				if (!self::convertType($res[$i], $type)) {
					throw new Nette\InvalidArgumentException(sprintf(
						'Argument $%s passed to %s must be %s, %s given.',
						$name,
						Reflection::toString($method),
						$type,
						get_debug_type($args[$name]),
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
					'Missing parameter $%s required by %s',
					$name,
					Reflection::toString($method),
				));
			}
		}

		return $res;
	}


	/**
	 * Converts list of arguments to named parameters & check types.
	 * @param  \ReflectionParameter[]  $missing arguments
	 * @throws InvalidLinkException
	 * @internal
	 */
	public static function toParameters(
		\ReflectionMethod $method,
		array &$args,
		array $supplemental = [],
		?array &$missing = null,
	): void
	{
		$i = 0;
		foreach ($method->getParameters() as $param) {
			$type = self::getType($param);
			$name = $param->getName();

			if (array_key_exists($i, $args)) {
				$args[$name] = $args[$i];
				unset($args[$i]);
				$i++;

			} elseif (array_key_exists($name, $args)) {
				// continue with process

			} elseif (array_key_exists($name, $supplemental)) {
				$args[$name] = $supplemental[$name];
			}

			if (!isset($args[$name])) {
				if (
					!$param->isDefaultValueAvailable()
					&& !$param->allowsNull()
					&& $type !== 'scalar'
					&& $type !== 'array'
					&& $type !== 'iterable'
				) {
					$missing[] = $param;
					unset($args[$name]);
				}

				continue;
			}

			if (!self::convertType($args[$name], $type)) {
				throw new InvalidLinkException(sprintf(
					'Argument $%s passed to %s must be %s, %s given.',
					$name,
					Reflection::toString($method),
					$type,
					get_debug_type($args[$name]),
				));
			}

			$def = $param->isDefaultValueAvailable()
				? $param->getDefaultValue()
				: null;
			if ($args[$name] === $def || ($def === null && $args[$name] === '')) {
				$args[$name] = null; // value transmit is unnecessary
			}
		}

		if (array_key_exists($i, $args)) {
			throw new InvalidLinkException('Passed more parameters than method ' . Reflection::toString($method) . ' expects.');
		}
	}


	/**
	 * Lossless type conversion.
	 */
	public static function convertType(mixed &$val, string $types): bool
	{
		$scalars = ['string' => 1, 'int' => 1, 'float' => 1, 'bool' => 1, 'true' => 1, 'false' => 1];
		$testable = ['iterable' => 1, 'object' => 1, 'array' => 1, 'null' => 1];

		foreach (explode('|', ltrim($types, '?')) as $type) {
			if (match (true) {
				isset($scalars[$type]) => self::castScalar($val, $type),
				isset($testable[$type]) => "is_$type"($val),
				$type === 'scalar' => !is_array($val), // special type due to historical reasons
				$type === 'mixed' => true,
				$type === 'callable' => false, // intentionally disabled for security reasons
				default => $val instanceof $type,
			}) {
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
		if ($type === 'float') {
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


	public static function getType(\ReflectionParameter|\ReflectionProperty $item): string
	{
		if ($type = $item->getType()) {
			return (string) $type;
		}
		$default = $item instanceof \ReflectionProperty || $item->isDefaultValueAvailable()
			? $item->getDefaultValue()
			: null;
		return $default === null ? 'scalar' : get_debug_type($default);
	}
}
