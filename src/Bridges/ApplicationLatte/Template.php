<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Bridges\ApplicationLatte;

use Nette;
use Latte;


/**
 * Latte powered template.
 *
 * @author     David Grudl
 */
class Template extends Nette\Object implements Nette\Application\UI\ITemplate
{
	/** @var Latte\Engine */
	private $latte;

	/** @var string */
	private $file;

	/** @var array */
	private $params = [];


	public function __construct(Latte\Engine $latte)
	{
		$this->latte = $latte;
	}


	/**
	 * @return Latte\Engine
	 */
	public function getLatte()
	{
		return $this->latte;
	}


	/**
	 * Renders template to output.
	 * @return void
	 */
	public function render($file = NULL, array $params = [])
	{
		$this->latte->render($file ?: $this->file, $params + $this->params);
	}


	/**
	 * Renders template to string.
	 * @param  can throw exceptions? (hidden parameter)
	 * @return string
	 */
	public function __toString()
	{
		try {
			return $this->latte->renderToString($this->file, $this->params);
		} catch (\Exception $e) {
			if (func_num_args()) {
				throw $e;
			}
			trigger_error("Exception in " . __METHOD__ . "(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", E_USER_ERROR);
		}
	}


	/********************* template filters & helpers ****************d*g**/


	/**
	 * Registers run-time filter.
	 * @param  string|NULL
	 * @param  callable
	 * @return self
	 */
	public function addFilter($name, $callback)
	{
		return $this->latte->addFilter($name, $callback);
	}


	/**
	 * Alias for addFilter()
	 * @deprecated
	 */
	public function registerHelper($name, $callback)
	{
		//trigger_error(__METHOD__ . '() is deprecated, use getLatte()->addFilter().', E_USER_DEPRECATED);
		return $this->latte->addFilter($name, $callback);
	}


	/**
	 * Alias for addFilterLoader()
	 * @deprecated
	 */
	public function registerHelperLoader($loader)
	{
		trigger_error(__METHOD__ . '() is deprecated, use dynamic getLatte()->addFilter().', E_USER_DEPRECATED);
		$latte = $this->latte;
		$this->latte->addFilter(NULL, function ($name) use ($loader, $latte) {
			if ($callback = call_user_func($loader, $name)) {
				$latte->addFilter($name, $callback);
			}
		});
		return $this;
	}


	/**
	 * Sets translate adapter.
	 * @return self
	 */
	public function setTranslator(Nette\Localization\ITranslator $translator = NULL)
	{
		$this->latte->addFilter('translate', $translator === NULL ? NULL : [$translator, 'translate']);
		return $this;
	}


	/********************* template parameters ****************d*g**/


	/**
	 * Sets the path to the template file.
	 * @param  string
	 * @return self
	 */
	public function setFile($file)
	{
		$this->file = $file;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getFile()
	{
		return $this->file;
	}


	/**
	 * Adds new template parameter.
	 * @return self
	 */
	public function add($name, $value)
	{
		if (array_key_exists($name, $this->params)) {
			throw new Nette\InvalidStateException("The variable '$name' already exists.");
		}
		$this->params[$name] = $value;
		return $this;
	}


	/**
	 * Sets all parameters.
	 * @param  array
	 * @return self
	 */
	public function setParameters(array $params)
	{
		$this->params = $params + $this->params;
		return $this;
	}


	/**
	 * Returns array of all parameters.
	 * @return array
	 */
	public function getParameters()
	{
		return $this->params;
	}


	/**
	 * @deprecated
	 */
	public function __call($name, $args)
	{
		trigger_error('Invoking filters on Template object is deprecated, use getLatte()->invokeFilter().', E_USER_DEPRECATED);
		return $this->latte->invokeFilter($name, $args);
	}


	/**
	 * Sets a template parameter. Do not call directly.
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->params[$name] = $value;
	}


	/**
	 * Returns a template parameter. Do not call directly.
	 * @return mixed  value
	 */
	public function &__get($name)
	{
		if (!array_key_exists($name, $this->params)) {
			trigger_error("The variable '$name' does not exist in template.", E_USER_NOTICE);
		}

		return $this->params[$name];
	}


	/**
	 * Determines whether parameter is defined. Do not call directly.
	 * @return bool
	 */
	public function __isset($name)
	{
		return isset($this->params[$name]);
	}


	/**
	 * Removes a template parameter. Do not call directly.
	 * @param  string    name
	 * @return void
	 */
	public function __unset($name)
	{
		unset($this->params[$name]);
	}

}
