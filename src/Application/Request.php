<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application;

use Nette;
use function func_num_args, strcasecmp;


/**
 * Presenter request.
 *
 * @property-deprecated string $presenterName
 * @property-deprecated array<string,mixed> $parameters
 * @property-deprecated array<string,mixed> $post
 * @property-deprecated array<string,mixed> $files
 * @property-deprecated ?string $method
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
		/** @var array<string, mixed> */
		private array $params = [],
		/** @var array<string, mixed> */
		private array $post = [],
		/** @var array<string, mixed> */
		private array $files = [],
		/** @var array<string, bool> */
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
	 * Returns the presenter name.
	 */
	public function getPresenterName(): string
	{
		return $this->name;
	}


	/**
	 * Sets variables provided to the presenter.
	 * @param  array<string, mixed>  $params
	 */
	public function setParameters(array $params): static
	{
		$this->params = $params;
		return $this;
	}


	/**
	 * Returns all variables provided to the presenter (usually via URL).
	 * @return array<string, mixed>
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
	 * @param  array<string, mixed>  $params
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
			: ($this->post[(string) $key] ?? null);
	}


	/**
	 * Sets all uploaded files.
	 * @param  array<string, mixed>  $files
	 */
	public function setFiles(array $files): static
	{
		$this->files = $files;
		return $this;
	}


	/**
	 * Returns all uploaded files.
	 * @return array<string, mixed>
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
		return strcasecmp($this->method ?? '', $method) === 0;
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


	/** @return array<string, mixed> */
	public function toArray(): array
	{
		$params = $this->params;
		$params['presenter'] = $this->name;
		return $params;
	}
}
