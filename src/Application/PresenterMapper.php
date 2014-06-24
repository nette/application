<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application;

use Nette;


class PresenterMapper extends Nette\Object implements IPresenterMapper
{
	/** @var array[] of module => splited mask */
	private $mapping = array(
		'*' => array('', '*Module\\', '*Presenter'),
		'Nette' => array('NetteModule\\', '*\\', '*Presenter'),
	);


	/**
	 * Sets mapping as pairs [module => mask]
	 * @return self
	 */
	public function setMapping(array $mapping)
	{
		foreach ($mapping as $module => $mask) {
			if (!preg_match('#^\\\\?([\w\\\\]*\\\\)?(\w*\*\w*?\\\\)?([\w\\\\]*\*\w*)\z#', $mask, $m)) {
				throw new Nette\InvalidStateException("Invalid mapping mask '$mask'.");
			}
			$this->mapping[$module] = array($m[1], $m[2] ?: '*Module\\', $m[3]);
		}
		return $this;
	}


	/**
	 * Formats presenter class name from its name.
	 * @param  string
	 * @return string
	 */
	public function formatPresenterClass($presenter)
	{
		$parts = explode(':', $presenter);
		$mapping = isset($parts[1], $this->mapping[$parts[0]])
			? $this->mapping[array_shift($parts)]
			: $this->mapping['*'];

		while ($part = array_shift($parts)) {
			$mapping[0] .= str_replace('*', $part, $mapping[$parts ? 1 : 2]);
		}
		return $mapping[0];
	}


	/**
	 * Formats presenter name from class name.
	 * @param  string
	 * @return string
	 */
	public function unformatPresenterClass($class)
	{
		foreach ($this->mapping as $module => $mapping) {
			$mapping = str_replace(array('\\', '*'), array('\\\\', '(\w+)'), $mapping);
			if (preg_match("#^\\\\?$mapping[0]((?:$mapping[1])*)$mapping[2]\\z#i", $class, $matches)) {
				return ($module === '*' ? '' : $module . ':')
				. preg_replace("#$mapping[1]#iA", '$1:', $matches[1]) . $matches[3];
			}
		}
	}

} 