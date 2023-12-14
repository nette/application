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
	private Latte\Engine $latte;
	private ?string $file = null;


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
	 */
	public function __toString(): string
	{
		return $this->latte->renderToString($this->file, $this->getParameters());
	}


	/********************* template filters & helpers ****************d*g**/


	/**
	 * Registers run-time filter.
	 */
	public function addFilter(?string $name, callable $callback): static
	{
		$this->latte->addFilter($name, $callback);
		return $this;
	}


	/**
	 * Registers run-time function.
	 */
	public function addFunction(string $name, callable $callback): static
	{
		$this->latte->addFunction($name, $callback);
		return $this;
	}


	/**
	 * Sets translate adapter.
	 */
	public function setTranslator(?Nette\Localization\Translator $translator, ?string $language = null): static
	{
		if (version_compare(Latte\Engine::VERSION, '3', '<')) {
			$this->latte->addFilter(
				'translate',
				fn(Latte\Runtime\FilterInfo $fi, ...$args): string => $translator === null
						? $args[0]
						: $translator->translate(...$args),
			);
		} else {
			$this->latte->addExtension(new Latte\Essential\TranslatorExtension($translator, $language));
		}
		return $this;
	}


	/********************* template parameters ****************d*g**/


	/**
	 * Sets the path to the template file.
	 */
	public function setFile(string $file): static
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
			if ($prop->isInitialized($this)) {
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
