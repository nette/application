<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application;

use Nette;


/**
 * Presenter request.
 *
 * @property string $presenterName
 * @property array $parameters
 * @property array $post
 * @property array $files
 * @property string|null $method
 */
final class Request
{
	use Nette\SmartObject;

	/** method */
	public const FORWARD = 'FORWARD';

	/** flag */
	public const RESTORED = 'restored';

	/** flag */
	public const VARYING = 'varying';


	public function __construct(
		private string $name,
		private ?string $method = null,
		private array $params = [],
		private array $post = [],
		private array $files = [],
		private array $flags = [],
	) {
	}


	/**
	 * Sets the presenter name.
	 */
	public function setPresenterName(string $name): static
	{
		$this->name = $name;
		return $this;
	}


	/**
	 * Retrieve the presenter name.
	 */
	public function getPresenterName(): string
	{
		return $this->name;
	}


	/**
	 * Sets variables provided to the presenter.
	 */
	public function setParameters(array $params): static
	{
		$this->params = $params;
		return $this;
	}


	/**
	 * Returns all variables provided to the presenter (usually via URL).
	 */
	public function getParameters(): array
	{
		return $this->params;
	}


	/**
	 * Returns a parameter provided to the presenter.
	 */
	public function getParameter(string $key): mixed
	{
		return $this->params[$key] ?? null;
	}


	/**
	 * Sets variables provided to the presenter via POST.
	 */
	public function setPost(array $params): static
	{
		$this->post = $params;
		return $this;
	}


	/**
	 * Returns a variable provided to the presenter via POST.
	 * If no key is passed, returns the entire array.
	 */
	public function getPost(?string $key = null): mixed
	{
		return func_num_args() === 0
			? $this->post
			: ($this->post[$key] ?? null);
	}


	/**
	 * Sets all uploaded files.
	 */
	public function setFiles(array $files): static
	{
		$this->files = $files;
		return $this;
	}


	/**
	 * Returns all uploaded files.
	 */
	public function getFiles(): array
	{
		return $this->files;
	}


	/**
	 * Sets the method.
	 */
	public function setMethod(?string $method): static
	{
		$this->method = $method;
		return $this;
	}


	/**
	 * Returns the method.
	 */
	public function getMethod(): ?string
	{
		return $this->method;
	}


	/**
	 * Checks if the method is the given one.
	 */
	public function isMethod(string $method): bool
	{
		return strcasecmp((string) $this->method, $method) === 0;
	}


	/**
	 * Sets the flag.
	 */
	public function setFlag(string $flag, bool $value = true): static
	{
		$this->flags[$flag] = $value;
		return $this;
	}


	/**
	 * Checks the flag.
	 */
	public function hasFlag(string $flag): bool
	{
		return !empty($this->flags[$flag]);
	}


	public function toArray(): array
	{
		$params = $this->params;
		$params['presenter'] = $this->name;
		return $params;
	}
}
