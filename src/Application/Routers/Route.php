<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\Routers;

use Nette;


/**
 * The bidirectional route is responsible for mapping
 * HTTP request to an array for dispatch and vice-versa.
 */
class Route extends Nette\Routing\Route implements Nette\Application\IRouter
{
	private const
		PRESENTER_KEY = 'presenter',
		MODULE_KEY = 'module';

	private const UI_META = [
		'module' => [
			self::PATTERN => '[a-z][a-z0-9.-]*',
			self::FILTER_IN => [self::class, 'path2presenter'],
			self::FILTER_OUT => [self::class, 'presenter2path'],
		],
		'presenter' => [
			self::PATTERN => '[a-z][a-z0-9.-]*',
			self::FILTER_IN => [self::class, 'path2presenter'],
			self::FILTER_OUT => [self::class, 'presenter2path'],
		],
		'action' => [
			self::PATTERN => '[a-z][a-z0-9-]*',
			self::FILTER_IN => [self::class, 'path2action'],
			self::FILTER_OUT => [self::class, 'action2path'],
		],
	];

	/** @var int */
	private $flags;


	/**
	 * @param  string  $mask  e.g. '<presenter>/<action>/<id \d{1,3}>'
	 * @param  array|string|\Closure  $metadata  default values or metadata or callback for NetteModule\MicroPresenter
	 */
	public function __construct(string $mask, $metadata = [], int $flags = 0)
	{
		if (is_string($metadata)) {
			[$presenter, $action] = Nette\Application\Helpers::splitName($metadata);
			if (!$presenter) {
				throw new Nette\InvalidArgumentException("Second argument must be array or string in format Presenter:action, '$metadata' given.");
			}
			$metadata = [self::PRESENTER_KEY => $presenter];
			if ($action !== '') {
				$metadata['action'] = $action;
			}
		} elseif ($metadata instanceof \Closure) {
			$metadata = [
				self::PRESENTER_KEY => 'Nette:Micro',
				'callback' => $metadata,
			];
		}

		$this->defaultMeta += self::UI_META;
		$this->flags = $flags;
		parent::__construct($mask, $metadata);
	}


	/**
	 * Maps HTTP request to an array.
	 */
	public function match(Nette\Http\IRequest $httpRequest): ?array
	{
		$params = parent::match($httpRequest);

		if ($params === null) {
			return null;
		} elseif (!isset($params[self::PRESENTER_KEY])) {
			throw new Nette\InvalidStateException('Missing presenter in route definition.');
		} elseif (!is_string($params[self::PRESENTER_KEY])) {
			return null;
		}

		$presenter = $params[self::PRESENTER_KEY] ?? null;
		if (isset($this->getMetadata()[self::MODULE_KEY], $params[self::MODULE_KEY]) && is_string($presenter)) {
			$params[self::PRESENTER_KEY] = $params[self::MODULE_KEY] . ':' . $params[self::PRESENTER_KEY];
		}
		unset($params[self::MODULE_KEY]);

		return $params;
	}


	/**
	 * Constructs absolute URL from array.
	 */
	public function constructUrl(array $params, Nette\Http\UrlScript $refUrl): ?string
	{
		if ($this->flags & self::ONE_WAY) {
			return null;
		}

		$metadata = $this->getMetadata();
		if (isset($metadata[self::MODULE_KEY])) { // try split into module and [submodule:]presenter parts
			$presenter = $params[self::PRESENTER_KEY];
			$module = $metadata[self::MODULE_KEY];
			$a = isset($module['fixity'], $module[self::VALUE])
				&& strncmp($presenter, $module[self::VALUE] . ':', strlen($module[self::VALUE]) + 1) === 0
				? strlen($module[self::VALUE])
				: strrpos($presenter, ':');
			if ($a === false) {
				$params[self::MODULE_KEY] = isset($module[self::VALUE]) ? '' : null;
			} else {
				$params[self::MODULE_KEY] = substr($presenter, 0, $a);
				$params[self::PRESENTER_KEY] = substr($presenter, $a + 1);
			}
		}

		return parent::constructUrl($params, $refUrl);
	}


	/** @internal */
	public function getConstantParameters(): array
	{
		$res = parent::getConstantParameters();
		if (isset($res[self::MODULE_KEY], $res[self::PRESENTER_KEY])) {
			$res[self::PRESENTER_KEY] = $res[self::MODULE_KEY] . ':' . $res[self::PRESENTER_KEY];
		} elseif (isset($this->getMetadata()[self::MODULE_KEY])) {
			unset($res[self::PRESENTER_KEY]);
		}
		unset($res[self::MODULE_KEY]);
		return $res;
	}


	/**
	 * Returns flags.
	 */
	public function getFlags(): int
	{
		return $this->flags;
	}


	/********************* Inflectors ****************d*g**/


	/**
	 * camelCaseAction name -> dash-separated.
	 */
	public static function action2path(string $s): string
	{
		$s = preg_replace('#(.)(?=[A-Z])#', '$1-', $s);
		$s = strtolower($s);
		$s = rawurlencode($s);
		return $s;
	}


	/**
	 * dash-separated -> camelCaseAction name.
	 */
	public static function path2action(string $s): string
	{
		$s = preg_replace('#-(?=[a-z])#', ' ', $s);
		$s = lcfirst(ucwords($s));
		$s = str_replace(' ', '', $s);
		return $s;
	}


	/**
	 * PascalCase:Presenter name -> dash-and-dot-separated.
	 */
	public static function presenter2path(string $s): string
	{
		$s = strtr($s, ':', '.');
		$s = preg_replace('#([^.])(?=[A-Z])#', '$1-', $s);
		$s = strtolower($s);
		$s = rawurlencode($s);
		return $s;
	}


	/**
	 * dash-and-dot-separated -> PascalCase:Presenter name.
	 */
	public static function path2presenter(string $s): string
	{
		$s = preg_replace('#([.-])(?=[a-z])#', '$1 ', $s);
		$s = ucwords($s);
		$s = str_replace('. ', ':', $s);
		$s = str_replace('- ', '', $s);
		return $s;
	}
}
