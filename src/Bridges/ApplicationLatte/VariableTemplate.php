<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Bridges\ApplicationLatte;

use Latte;


/**
 * Variable $template for back compatibility.
 * @internal
 */
class VariableTemplate extends Latte\Template
{
	/** @var Latte\Template */
	private $template;


	public function __construct(Latte\Template $template)
	{
		$this->template = $template;
		$template->params['template'] = $this;
	}

	/**
	 * Call a template run-time filter.
	 */
	public function __call($name, $args)
	{
		return call_user_func_array($this->template->filters->$name, $args);
	}


	/**
	 * Sets a template parameter.
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->template->params[$name] = $value;
	}


	/**
	 * Returns a template parameter.
	 * @return mixed  value
	 */
	public function &__get($name)
	{
		if (!array_key_exists($name, $this->template->params)) {
			trigger_error("The variable '$name' does not exist in template.");
		}
		return $this->template->params[$name];
	}


	/**
	 * Determines whether parameter is defined.
	 * @return bool
	 */
	public function __isset($name)
	{
		return isset($this->template->params[$name]);
	}


	/**
	 * Removes a template parameter.
	 * @param  string    name
	 * @return void
	 */
	public function __unset($name)
	{
		unset($this->template->params[$name]);
	}


	/**
	 * Returns array of all parameters.
	 * @return array
	 */
	public function getParameters()
	{
		return $this->template->getParameters();
	}

}
