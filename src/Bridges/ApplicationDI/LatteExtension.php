<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Bridges\ApplicationDI;

use Nette,
	Latte;


/**
 * Latte extension for Nette DI.
 *
 * @author     David Grudl
 * @author     Petr Morávek
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
		if (!class_exists('Latte\Engine')) {
			return;
		}

		$config = $this->validateConfig($this->defaults);
		$container = $this->getContainerBuilder();

		$latteFactory = $container->addDefinition($this->prefix('latteFactory'))
			->setClass('Latte\Engine')
			->addSetup('setTempDirectory', [$this->tempDir])
			->addSetup('setAutoRefresh', [$this->debugMode])
			->addSetup('setContentType', [$config['xhtml'] ? Latte\Compiler::CONTENT_XHTML : Latte\Compiler::CONTENT_HTML])
			->addSetup('Nette\Utils\Html::$xhtml = ?', [(bool) $config['xhtml']])
			->setImplement('Nette\Bridges\ApplicationLatte\ILatteFactory');

		$container->addDefinition($this->prefix('templateFactory'))
			->setClass('Nette\Application\UI\ITemplateFactory')
			->setFactory('Nette\Bridges\ApplicationLatte\TemplateFactory');

		$container->addDefinition('nette.latte')
			->setClass('Latte\Engine')
			->addSetup('::trigger_error', ['Service nette.latte is deprecated, implement Nette\Bridges\ApplicationLatte\ILatteFactory.', E_USER_DEPRECATED])
			->addSetup('setTempDirectory', [$this->tempDir])
			->addSetup('setAutoRefresh', [$this->debugMode])
			->addSetup('setContentType', [$config['xhtml'] ? Latte\Compiler::CONTENT_XHTML : Latte\Compiler::CONTENT_HTML])
			->addSetup('Nette\Utils\Html::$xhtml = ?', [(bool) $config['xhtml']])
			->setAutowired(FALSE);

		foreach ($config['macros'] as $macro) {
			if (strpos($macro, '::') === FALSE && class_exists($macro)) {
				$macro .= '::install';
			}
			$this->addMacro($macro);
		}

		if (class_exists('Nette\Templating\FileTemplate')) {
			$container->addDefinition('nette.template')
				->setFactory('Nette\Templating\FileTemplate')
				->addSetup('::trigger_error', ['Service nette.template is deprecated.', E_USER_DEPRECATED])
				->addSetup('registerFilter', [new Nette\DI\Statement([$latteFactory, 'create'])])
				->addSetup('registerHelperLoader', ['Nette\Templating\Helpers::loader'])
				->setAutowired(FALSE);
		}

		if ($this->name === 'latte') {
			$container->addAlias('nette.latteFactory', $this->prefix('latteFactory'));
			$container->addAlias('nette.templateFactory', $this->prefix('templateFactory'));
		}
	}


	/**
	 * @param  callable
	 * @return void
	 */
	public function addMacro($macro)
	{
		Nette\Utils\Validators::assert($macro, 'callable');

		$container = $this->getContainerBuilder();
		$container->getDefinition('nette.latte')
			->addSetup('?->onCompile[] = function($engine) { ' . $macro . '($engine->getCompiler()); }', ['@self']);

		$container->getDefinition($this->prefix('latteFactory'))
			->addSetup('?->onCompile[] = function($engine) { ' . $macro . '($engine->getCompiler()); }', ['@self']);
	}

}
