<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationDI;

use Latte;
use Nette;


/**
 * Latte extension for Nette DI.
 */
final class LatteExtension extends Nette\DI\CompilerExtension
{
	public $defaults = [
		'xhtml' => false,
		'macros' => [],
		'templateClass' => null,
		'strictTypes' => false,
	];

	/** @var bool */
	private $debugMode;

	/** @var string */
	private $tempDir;


	public function __construct($tempDir, bool $debugMode = false)
	{
		$this->tempDir = $tempDir;
		$this->debugMode = $debugMode;
	}


	public function loadConfiguration()
	{
		if (!class_exists(Latte\Engine::class)) {
			return;
		}

		$config = $this->validateConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		$latteFactory = $builder->addDefinition($this->prefix('latteFactory'))
			->setFactory(Latte\Engine::class)
			->addSetup('setTempDirectory', [$this->tempDir])
			->addSetup('setAutoRefresh', [$this->debugMode])
			->addSetup('setContentType', [$config['xhtml'] ? Latte\Compiler::CONTENT_XHTML : Latte\Compiler::CONTENT_HTML])
			->addSetup('Nette\Utils\Html::$xhtml = ?', [(bool) $config['xhtml']])
			->setImplement(Nette\Bridges\ApplicationLatte\ILatteFactory::class);

		if ($config['strictTypes']) {
			$latteFactory->addSetup('setStrictTypes', [true]);
		}

		$builder->addDefinition($this->prefix('templateFactory'))
			->setClass(Nette\Application\UI\ITemplateFactory::class)
			->setFactory(Nette\Bridges\ApplicationLatte\TemplateFactory::class)
			->setArguments(['templateClass' => $config['templateClass']]);

		foreach ($config['macros'] as $macro) {
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
		$definition = $builder->getDefinition($this->prefix('latteFactory'));

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
