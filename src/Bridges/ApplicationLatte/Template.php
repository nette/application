<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte;

use Latte;
use Nette;


/**
 * Latte powered template.
 */
class Template implements Nette\Application\UI\ITemplate
{
	use Nette\SmartObject;

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


	final public function getLatte(): Latte\Engine
	{
		return $this->latte;
	}


	/**
	 * Renders template to output.
	 */
	public function render(string $file = null, array $params = []): void
	{
		$this->latte->render($file ?: $this->file, $params + $this->params);
	}


	/**
	 * Renders template to output.
	 */
	public function renderToString(string $file = null, array $params = []): string
	{
		return $this->latte->renderToString($file ?: $this->file, $params + $this->params);
	}


	/**
	 * Renders template to string.
	 * @param  can throw exceptions? (hidden parameter)
	 */
	public function __toString(): string
	{
		try {
			return $this->latte->renderToString($this->file, $this->params);
		} catch (\Throwable $e) {
			if (func_num_args() || PHP_VERSION_ID >= 70400) {
				throw $e;
			}
			trigger_error('Exception in ' . __METHOD__ . "(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", E_USER_ERROR);
			return '';
		}
	}


	/********************* template filters & helpers ****************d*g**/


	/**
	 * Registers run-time filter.
	 * @return static
	 */
	public function addFilter(?string $name, callable $callback)
	{
		$this->latte->addFilter($name, $callback);
		return $this;
	}


	/**
	 * Registers run-time function.
	 * @return static
	 */
	public function addFunction(string $name, callable $callback)
	{
		$this->latte->addFunction($name, $callback);
		return $this;
	}


	/**
	 * Sets translate adapter.
	 * @return static
	 */
	public function setTranslator(?Nette\Localization\ITranslator $translator)
	{
		$this->latte->addFilter('translate', function (Latte\Runtime\FilterInfo $fi, ...$args) use ($translator): string {
			return $translator === null ? $args[0] : $translator->translate(...$args);
		});
		return $this;
	}


	/********************* template parameters ****************d*g**/


	/**
	 * Sets the path to the template file.
	 * @return static
	 */
	public function setFile(string $file)
	{
		$this->file = $file;
		return $this;
	}


	final public function getFile(): ?string
	{
		return $this->file;
	}


	/**
	 * Adds new template parameter.
	 * @return static
	 */
	public function add(string $name, $value)
	{
		if (array_key_exists($name, $this->params)) {
			throw new Nette\InvalidStateException("The variable '$name' already exists.");
		}
		$this->params[$name] = $value;
		return $this;
	}


	/**
	 * Sets all parameters.
	 * @return static
	 */
	public function setParameters(array $params)
	{
		$this->params = $params + $this->params;
		return $this;
	}


	/**
	 * Returns array of all parameters.
	 */
	final public function getParameters(): array
	{
		return $this->params;
	}


	/**
	 * Sets a template parameter. Do not call directly.
	 */
	public function __set($name, $value): void
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
	 */
	public function __isset($name)
	{
		return isset($this->params[$name]);
	}


	/**
	 * Removes a template parameter. Do not call directly.
	 */
	public function __unset(string $name): void
	{
		unset($this->params[$name]);
	}
}
