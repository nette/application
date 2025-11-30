<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte;

use Latte;
use Nette\Application\UI;
use Nette\Utils\Helpers;
use Nette\Utils\Type;
use Nette\Utils\Validators;
use function is_object, strlen;


/**
 * On-the-fly template class generator.
 */
#[\AllowDynamicProperties]
final class TemplateGenerator extends Template
{
	private string $className;
	private ?self $parent = null;

	/** @var array<string, Type> */
	private array $properties = [];
	private bool $trackProperties = true;


	public function __construct(
		Latte\Engine $latte,
		?string $className = null,
		?UI\Control $control = null,
	) {
		parent::__construct($latte);

		$this->className = $className && $className !== DefaultTemplate::class
			? $className
			: preg_replace('#Control|Presenter$#', '', $control::class) . 'Template';

		if (!class_exists($this->className)) {
			$this->createTemplateClass($control);
			$this->updateControlPhpDoc($control);
		}
		$this->loadTemplateClass();
	}


	public function __set($name, $value): void
	{
		if ($this->trackProperties) {
			($this->findPropertyOwner($name) ?? $this)->ensureProperty($name, $value);
		}
		$this->$name = $value;
	}


	public function addDefaultVariable(string $name, mixed $value): void
	{
		$owner = $this->findPropertyOwner($name) ?? $this;
		if (!isset($owner->properties[$name])) {
			if (is_object($value)) {
				if (PHP_VERSION_ID >= 80400) {
					$rc = new \ReflectionClass($value);
					$rc->initializeLazyObject($value);
					$value = ($rc)->newLazyProxy(fn() => $this->ensureProperty($name, $value));
				}

			} else {
				$value = new class (fn() => $this->ensureProperty($name, $value)) implements \IteratorAggregate {
					public function __construct(
						private \Closure $cb,
					) {
					}


					public function __toString(): string
					{
						return ($this->cb)(); // basePath & baseUrl
					}


					public function getIterator(): \Traversable
					{
						yield from ($this->cb)(); // flashes
					}
				};
			}
		}

		$this->trackProperties = false;
		$this->$name = $value;
		$this->trackProperties = true;
	}


	private function ensureProperty(string $name, mixed $value): mixed
	{
		$declaredType = $this->properties[$name] ?? null;
		$actualType = Type::fromValue($value);
		if (!$declaredType) {
			$this->properties[$name] = $actualType;
			$this->updateTemplateClass($name);
		} elseif (!$declaredType->allows($actualType)) {
			$this->properties[$name] = $declaredType->with($actualType);
			$this->updateTemplateClass($name);
		}
		return $value;
	}


	private function findPropertyOwner(string $name): ?self
	{
		return match (true) {
			isset($this->properties[$name]) => $this,
			$this->parent !== null => $this->parent->findPropertyOwner($name),
			default => null,
		};
	}


	/********************* generator ****************d*g**/


	private function createTemplateClass(UI\Control $control): void
	{
		[$namespace, $shortName] = Helpers::splitClassName($this->className);
		$namespaceCode = $namespace ? PHP_EOL . "namespace $namespace;" . PHP_EOL : '';
		$fileName = dirname((new \ReflectionClass($control))->getFileName()) . '/' . $shortName . '.php';
		file_put_contents($fileName, <<<XX
			<?php

			declare(strict_types=1);
			$namespaceCode
			use Nette\\Bridges\\ApplicationLatte\\Template;

			class $shortName extends Template
			{
			}
			XX);
		require $fileName;
	}


	private function loadTemplateClass(): void
	{
		$rc = new \ReflectionClass($this->className);
		foreach ($rc->getProperties() as $prop) {
			if ($prop->getDeclaringClass() == $rc) { // intentionally ==
				$this->properties[$prop->getName()] = Type::fromReflection($prop);
			}
		}

		$parent = $rc->getParentClass()->getName();
		if ($parent !== Template::class) {
			$this->parent = new self($this->getLatte(), $parent);
		}
	}


	private function updateTemplateClass(string $name): void
	{
		$file = (new \ReflectionClass($this->className))->getFileName();
		if (!is_file($file)) {
			throw new \RuntimeException("Cannot update class file for {$this->className}, file not found.");
		}

		$src = file_get_contents($file);
		$typeDecl = preg_replace_callback(
			'/([\w\\\]+)/',
			fn($m) => Validators::isBuiltinType($m[1]) ? $m[1] : '\\' . $m[1],
			(string) $this->properties[$name],
		);
		$decl = "\tpublic $typeDecl \$$name";

		$src = preg_replace(
			'/^\s*public\s+[^$;]*\s*\$' . $name . '\b/m',
			$decl,
			$src,
			count: $count,
		);
		if (!$count) {
			if ($pos = strrpos($src, '}')) {
				$src = substr_replace($src, $decl . ';' . PHP_EOL, $pos, 0);
			} else {
				throw new \RuntimeException("Cannot update class file for {$this->className}, invalid syntax.");
			}
		}
		file_put_contents($file, $src);
	}


	private function updateControlPhpDoc(UI\Control $control): void
	{
		$rc = new \ReflectionClass($control);
		$doc = $rc->getDocComment();
		$content = file_get_contents($rc->getFileName());
		$nl = PHP_EOL;
		$decl = '* @property-read ' . Helpers::splitClassName($this->className)[1] . ' $template';

		if (!$doc) {
			$content = preg_replace(
				'/^((final\s+)class\s+' . $rc->getShortName() . ')/m',
				"/**$nl $decl$nl */$nl$1",
				$content,
			);
		} elseif (!preg_match('/@property(-read)?\s+.*\$template/', $doc)) {
			$newDoc = preg_replace('~(\s*)\*/\s*$~', "$1$decl$0", $doc, 1);
			$content = str_replace($doc, $newDoc, $content);
		} else {
			return;
		}

		file_put_contents($rc->getFileName(), $content);
	}
}
