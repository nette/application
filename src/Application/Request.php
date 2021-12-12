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
	public const SECURED = 'secured';

	/** flag */
	public const RESTORED = 'restored';

	/** flag */
	public const VARYING = 'varying';

	/** @var string|null */
	private $method;

	/** @var array */
	private $flags = [];

	/** @var string */
	private $name;

	/** @var array */
	private $params;

	/** @var array */
	private $post;

	/** @var array */
	private $files;


	/**
	 * @param  string  $name  presenter name (module:module:presenter)
	 */
	public function __construct(
		string $name,
		?string $method = null,
		array $params = [],
		array $post = [],
		array $files = [],
		array $flags = []
	) {
		$this->name = $name;
		$this->method = $method;
		$this->params = $params;
		$this->post = $post;
		$this->files = $files;
		$this->flags = $flags;
	}


	/**
	 * Sets the presenter name.
	 * @return static
	 */
	public function setPresenterName(string $name)
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
	 * @return static
	 */
	public function setParameters(array $params)
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
	 * @return mixed
	 */
	public function getParameter(string $key)
	{
		return $this->params[$key] ?? null;
	}


	/**
	 * Sets variables provided to the presenter via POST.
	 * @return static
	 */
	public function setPost(array $params)
	{
		$this->post = $params;
		return $this;
	}


	/**
	 * Returns a variable provided to the presenter via POST.
	 * If no key is passed, returns the entire array.
	 * @return mixed
	 */
	public function getPost(?string $key = null)
	{
		return func_num_args() === 0
			? $this->post
			: ($this->post[$key] ?? null);
	}


	/**
	 * Sets all uploaded files.
	 * @return static
	 */
	public function setFiles(array $files)
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
	 * @return static
	 */
	public function setMethod(?string $method)
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
	 * @return static
	 */
	public function setFlag(string $flag, bool $value = true)
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
