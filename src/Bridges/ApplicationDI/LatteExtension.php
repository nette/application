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
	public $defaults = array(
		'xhtml' => FALSE,
		'macros' => array(),
	);

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
			->addSetup('setTempDirectory', array($this->tempDir))
			->addSetup('setAutoRefresh', array($this->debugMode))
			->addSetup('setContentType', array($config['xhtml'] ? Latte\Compiler::CONTENT_XHTML : Latte\Compiler::CONTENT_HTML))
			->addSetup('Nette\Utils\Html::$xhtml = ?', array((bool) $config['xhtml']))
			->setImplement('Nette\Bridges\ApplicationLatte\ILatteFactory');

		$container->addDefinition($this->prefix('templateFactory'))
			->setClass('Nette\Application\UI\ITemplateFactory')
			->setFactory('Nette\Bridges\ApplicationLatte\TemplateFactory');

		$container->addDefinition('nette.latte')
			->setClass('Latte\Engine')
			->addSetup('::trigger_error', array('Service nette.latte is deprecated, implement Nette\Bridges\ApplicationLatte\ILatteFactory.', E_USER_DEPRECATED))
			->addSetup('setTempDirectory', array($this->tempDir))
			->addSetup('setAutoRefresh', array($this->debugMode))
			->addSetup('setContentType', array($config['xhtml'] ? Latte\Compiler::CONTENT_XHTML : Latte\Compiler::CONTENT_HTML))
			->addSetup('Nette\Utils\Html::$xhtml = ?', array((bool) $config['xhtml']))
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
				->addSetup('::trigger_error', array('Service nette.template is deprecated.', E_USER_DEPRECATED))
				->addSetup('registerFilter', array(new Nette\DI\Statement(array($latteFactory, 'create'))))
				->addSetup('registerHelperLoader', array('Nette\Templating\Helpers::loader'))
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
			->addSetup('?->onCompile[] = function ($engine) { ' . $macro . '($engine->getCompiler()); }', array('@self'));

		$container->getDefinition($this->prefix('latteFactory'))
			->addSetup('?->onCompile[] = function ($engine) { ' . $macro . '($engine->getCompiler()); }', array('@self'));
	}

}
