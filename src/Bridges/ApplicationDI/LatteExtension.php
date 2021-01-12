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


/**
 * Latte extension for Nette DI.
 */
final class LatteExtension extends Nette\DI\CompilerExtension
{
	/** @var bool */
	private $debugMode;

	/** @var string */
	private $tempDir;


	public function __construct(string $tempDir, bool $debugMode = false)
	{
		$this->tempDir = $tempDir;
		$this->debugMode = $debugMode;
	}


	public function getConfigSchema(): Nette\Schema\Schema
	{
		return Expect::structure([
			'xhtml' => Expect::bool(false),
			'macros' => Expect::arrayOf('string'),
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

		$latteFactory = $builder->addFactoryDefinition($this->prefix('latteFactory'))
			->setImplement(ApplicationLatte\LatteFactory::class)
			->getResultDefinition()
				->setFactory(Latte\Engine::class)
				->addSetup('setTempDirectory', [$this->tempDir])
				->addSetup('setAutoRefresh', [$this->debugMode])
				->addSetup('setContentType', [$config->xhtml ? Latte\Compiler::CONTENT_XHTML : Latte\Compiler::CONTENT_HTML])
				->addSetup('Nette\Utils\Html::$xhtml = ?', [$config->xhtml]);

		if ($config->strictTypes) {
			$latteFactory->addSetup('setStrictTypes', [true]);
		}

		$builder->addDefinition($this->prefix('templateFactory'))
			->setType(Nette\Application\UI\TemplateFactory::class)
			->setFactory(ApplicationLatte\TemplateFactory::class)
			->setArguments(['templateClass' => $config->templateClass]);

		foreach ($config->macros as $macro) {
			$this->addMacro($macro);
		}

		if ($this->name === 'latte') {
			$builder->addAlias('nette.latteFactory', $this->prefix('latteFactory'));
			$builder->addAlias('nette.templateFactory', $this->prefix('templateFactory'));
		}
	}


	public function addMacro(string $macro): void
	{
		$builder = $this->getContainerBuilder();
		$definition = $builder->getDefinition($this->prefix('latteFactory'))->getResultDefinition();

		if (($macro[0] ?? null) === '@') {
			if (strpos($macro, '::') === false) {
				$method = 'install';
			} else {
				[$macro, $method] = explode('::', $macro);
			}
			$definition->addSetup('?->onCompile[] = function ($engine) { ?->' . $method . '($engine->getCompiler()); }', ['@self', $macro]);

		} else {
			if (strpos($macro, '::') === false && class_exists($macro)) {
				$macro .= '::install';
			}
			$definition->addSetup('?->onCompile[] = function ($engine) { ' . $macro . '($engine->getCompiler()); }', ['@self']);
		}
	}
}
