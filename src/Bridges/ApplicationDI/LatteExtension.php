<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationDI;

use Latte;
use Nette;
use Nette\Bridges\ApplicationLatte;
use Nette\DI\Definitions\Statement;
use Nette\Schema\Expect;
use Tracy;


/**
 * Latte extension for Nette DI.
 */
final class LatteExtension extends Nette\DI\CompilerExtension
{
	public function __construct(
		private readonly string $tempDir,
		private readonly bool $debugMode = false,
	) {
	}


	public function getConfigSchema(): Nette\Schema\Schema
	{
		return Expect::structure([
			'debugger' => Expect::anyOf(true, false, 'all'),
			'macros' => Expect::arrayOf('string'),
			'extensions' => Expect::arrayOf('string|Nette\DI\Definitions\Statement'),
			'templateClass' => Expect::string(),
			'strictTypes' => Expect::bool(false),
			'strictParsing' => Expect::bool(false),
			'phpLinter' => Expect::string(),
		]);
	}


	public function loadConfiguration(): void
	{
		if (!class_exists(Latte\Engine::class)) {
			return;
		}

		$config = $this->config;
		$builder = $this->getContainerBuilder();

		$latteFactory = $builder->addFactoryDefinition($this->prefix('latteFactory'))
			->setImplement(ApplicationLatte\LatteFactory::class)
			->getResultDefinition()
				->setFactory(Latte\Engine::class)
				->addSetup('setTempDirectory', [$this->tempDir])
				->addSetup('setAutoRefresh', [$this->debugMode])
				->addSetup('setStrictTypes', [$config->strictTypes]);

		if (version_compare(Latte\Engine::VERSION, '3', '<')) {
			foreach ($config->macros as $macro) {
				$this->addMacro($macro);
			}
		} else {
			$latteFactory->addSetup('setStrictParsing', [$config->strictParsing])
				->addSetup('enablePhpLinter', [$config->phpLinter]);

			if ($cache = $builder->getByType(Nette\Caching\Storage::class)) {
				$this->addExtension(new Statement(Nette\Bridges\CacheLatte\CacheExtension::class, [$builder->getDefinition($cache)]));
			}
			if (class_exists(Nette\Bridges\FormsLatte\FormsExtension::class)) {
				$this->addExtension(new Statement(Nette\Bridges\FormsLatte\FormsExtension::class));
			}

			foreach ($config->extensions as $extension) {
				$this->addExtension($extension);
			}
		}

		$builder->addDefinition($this->prefix('templateFactory'))
			->setFactory(ApplicationLatte\TemplateFactory::class)
			->setArguments(['templateClass' => $config->templateClass]);

		if ($this->name === 'latte') {
			$builder->addAlias('nette.latteFactory', $this->prefix('latteFactory'));
			$builder->addAlias('nette.templateFactory', $this->prefix('templateFactory'));
		}
	}


	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		if (
			$this->debugMode
			&& ($this->config->debugger ?? $builder->getByType(Tracy\Bar::class))
			&& class_exists(Latte\Bridges\Tracy\LattePanel::class)
		) {
			$factory = $builder->getDefinition($this->prefix('templateFactory'));
			$factory->addSetup([self::class, 'initLattePanel'], [$factory, 'all' => $this->config->debugger === 'all']);
		}
	}


	public static function initLattePanel(
		Nette\Application\UI\TemplateFactory $factory,
		Tracy\Bar $bar,
		bool $all = false,
	): void
	{
		if (!$factory instanceof ApplicationLatte\TemplateFactory) {
			return;
		}

		$factory->onCreate[] = function (ApplicationLatte\Template $template) use ($bar, $all) {
			$control = $template->getLatte()->getProviders()['uiControl'] ?? null;
			if ($all || $control instanceof Nette\Application\UI\Presenter) {
				$name = $all && $control ? (new \ReflectionObject($control))->getShortName() : '';
				if (version_compare(Latte\Engine::VERSION, '3', '<')) {
					$bar->addPanel(new Latte\Bridges\Tracy\LattePanel($template->getLatte(), $name));
				} else {
					$template->getLatte()->addExtension(new Latte\Bridges\Tracy\TracyExtension($name));
				}
			}
		};
	}


	public function addMacro(string $macro): void
	{
		$builder = $this->getContainerBuilder();
		$definition = $builder->getDefinition($this->prefix('latteFactory'))->getResultDefinition();

		if (($macro[0] ?? null) === '@') {
			if (str_contains($macro, '::')) {
				[$macro, $method] = explode('::', $macro);
			} else {
				$method = 'install';
			}

			$definition->addSetup('?->onCompile[] = function ($engine) { ?->' . $method . '($engine->getCompiler()); }', ['@self', $macro]);

		} else {
			if (!str_contains($macro, '::') && class_exists($macro)) {
				$macro .= '::install';
			}

			$definition->addSetup('?->onCompile[] = function ($engine) { ' . $macro . '($engine->getCompiler()); }', ['@self']);
		}
	}


	public function addExtension(Statement|string $extension): void
	{
		$extension = is_string($extension)
			? new Statement($extension)
			: $extension;

		$builder = $this->getContainerBuilder();
		$builder->getDefinition($this->prefix('latteFactory'))
			->getResultDefinition()
			->addSetup('addExtension', [$extension]);
	}
}
