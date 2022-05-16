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
class Template implements Nette\Application\UI\Template
{
	/** @var Latte\Engine */
	private $latte;

	/** @var string */
	private $file;


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
	public function render(?string $file = null, array $params = []): void
	{
		Nette\Utils\Arrays::toObject($params, $this);
		$this->latte->render($file ?: $this->file, $this);
	}


	/**
	 * Renders template to output.
	 */
	public function renderToString(?string $file = null, array $params = []): string
	{
		Nette\Utils\Arrays::toObject($params, $this);
		return $this->latte->renderToString($file ?: $this->file, $this);
	}


	/**
	 * Renders template to string.
	 * @param  can throw exceptions? (hidden parameter)
	 */
	public function __toString(): string
	{
		try {
			return $this->latte->renderToString($this->file, $this->getParameters());
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
	public function setTranslator(?Nette\Localization\Translator $translator, ?string $language = null)
	{
		if (version_compare(Latte\Engine::VERSION, '3', '<')) {
			$this->latte->addFilter(
				'translate',
				function (Latte\Runtime\FilterInfo $fi, ...$args) use ($translator): string {
					return $translator === null
						? $args[0]
						: $translator->translate(...$args);
				}
			);
		} else {
			$this->latte->addExtension(new TranslatorExtension($translator, $language));
		}
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
	 * Returns array of all parameters.
	 */
	final public function getParameters(): array
	{
		$res = [];
		foreach ((new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
			if (PHP_VERSION_ID < 70400 || $prop->isInitialized($this)) {
				$res[$prop->getName()] = $prop->getValue($this);
			}
		}

		return $res;
	}


	/**
	 * Prevents unserialization.
	 */
	final public function __wakeup()
	{
		throw new Nette\NotImplementedException('Object unserialization is not supported by class ' . static::class);
	}
}
