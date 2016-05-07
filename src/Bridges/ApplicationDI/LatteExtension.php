<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Bridges\ApplicationDI;

use Nette;
use Latte;


/**
 * Latte extension for Nette DI.
 */
class LatteExtension extends Nette\DI\CompilerExtension
{
	public $defaults = [
		'xhtml' => FALSE,
		'macros' => [],
	];

	/** @var bool */
	private $debugMode;

	/** @var string */
	private $tempDir;


	public function __construct($tempDir, $debugMode = FALSE)
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

		$builder->addDefinition($this->prefix('latteFactory'))
			->setClass(Latte\Engine::class)
			->addSetup('setTempDirectory', [$this->tempDir])
			->addSetup('setAutoRefresh', [$this->debugMode])
			->addSetup('setContentType', [$config['xhtml'] ? Latte\Compiler::CONTENT_XHTML : Latte\Compiler::CONTENT_HTML])
			->addSetup('Nette\Utils\Html::$xhtml = ?', [(bool) $config['xhtml']])
			->setImplement(Nette\Bridges\ApplicationLatte\ILatteFactory::class);

		$builder->addDefinition($this->prefix('templateFactory'))
			->setClass(Nette\Application\UI\ITemplateFactory::class)
			->setFactory(Nette\Bridges\ApplicationLatte\TemplateFactory::class);

		foreach ($config['macros'] as $macro) {
			if (strpos($macro, '::') === FALSE && class_exists($macro)) {
				$macro .= '::install';
			}
			$this->addMacro($macro);
		}

		if ($this->name === 'latte') {
			$builder->addAlias('nette.latteFactory', $this->prefix('latteFactory'));
			$builder->addAlias('nette.templateFactory', $this->prefix('templateFactory'));
		}
	}


	/**
	 * @param  callable
	 * @return void
	 */
	public function addMacro(callable $macro)
	{
		$builder = $this->getContainerBuilder();
		$builder->getDefinition($this->prefix('latteFactory'))
			->addSetup('?->onCompile[] = function ($engine) { ' . $macro . '($engine->getCompiler()); }', ['@self']);
	}

}
