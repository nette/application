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
use Nette\Schema\Expect;
use Tracy;


/**
 * Latte extension for Nette DI.
 */
final class LatteExtension extends Nette\DI\CompilerExtension
{
	private bool $debugMode;

	private string $tempDir;


	public function __construct(string $tempDir, bool $debugMode = false)
	{
		$this->tempDir = $tempDir;
		$this->debugMode = $debugMode;
	}


	public function getConfigSchema(): Nette\Schema\Schema
	{
		return Expect::structure([
			'debugger' => Expect::anyOf(true, false, 'all'),
			'macros' => Expect::arrayOf('string'),
			'extensions' => Expect::arrayOf('string|Nette\DI\Definitions\Statement'),
			'templateClass' => Expect::string(),
			'strictTypes' => Expect::bool(false),
		]);
	}


	public function loadConfiguration()
	{
		if (!class_exists(Latte\Engine::class)) {
			return;
		}

		$config = $this->config;
		$builder = $this->getContainerBuilder();

		$builder->addFactoryDefinition($this->prefix('latteFactory'))
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


	public function beforeCompile()
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
	) {
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


	/** @param Nette\DI\Definitions\Statement|string $extension */
	public function addExtension($extension): void
	{
		$extension = is_string($extension)
			? new Nette\DI\Definitions\Statement($extension)
			: $extension;

		$builder = $this->getContainerBuilder();
		$builder->getDefinition($this->prefix('latteFactory'))
			->getResultDefinition()
			->addSetup('addExtension', [$extension]);
	}
}
