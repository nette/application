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
	public $defaults = array(
		'xhtml' => FALSE,
		'macros' => array(),
	);

	private $xhtml;


	public function loadConfiguration()
	{
		if (!class_exists('Latte\Engine')) {
			return;
		}

		// back compatibility
		$config = $this->compiler->getConfig();
		if (isset($config['nette']['latte']) && !isset($config[$this->name])) {
			trigger_error("Configuration section 'nette.latte' is deprecated, use section '$this->name' instead.", E_USER_DEPRECATED);
			$config = Nette\DI\Config\Helpers::merge($config['nette']['latte'], $this->defaults);
		} else {
			$config = $this->getConfig($this->defaults);
		}
		if (isset($config['nette']['xhtml'])) {
			trigger_error("Configuration option 'nette.xhtml' is deprecated, use section '$this->name.xhtml' instead.", E_USER_DEPRECATED);
			$config['xhtml'] = $config['nette']['xhtml'];
		}

		$this->validate($config, $this->defaults, $this->name);
		$this->xhtml = $config['xhtml'];
		$container = $this->getContainerBuilder();

		$latteFactory = $container->addDefinition('nette.latteFactory')
			->setClass('Latte\Engine')
			->addSetup('setTempDirectory', array($container->expand('%tempDir%/cache/latte')))
			->addSetup('setAutoRefresh', array($container->parameters['debugMode']))
			->addSetup('setContentType', array($config['xhtml'] ? Latte\Compiler::CONTENT_XHTML : Latte\Compiler::CONTENT_HTML))
			->setImplement('Nette\Bridges\ApplicationLatte\ILatteFactory');

		$container->addDefinition('nette.templateFactory')
			->setClass('Nette\Application\UI\ITemplateFactory')
			->setFactory('Nette\Bridges\ApplicationLatte\TemplateFactory');

		$latte = $container->addDefinition('nette.latte')
			->setClass('Latte\Engine')
			->addSetup('::trigger_error', array('Service nette.latte is deprecated, implement Nette\Bridges\ApplicationLatte\ILatteFactory.', E_USER_DEPRECATED))
			->addSetup('setTempDirectory', array($container->expand('%tempDir%/cache/latte')))
			->addSetup('setAutoRefresh', array($container->parameters['debugMode']))
			->addSetup('setContentType', array($config['xhtml'] ? Latte\Compiler::CONTENT_XHTML : Latte\Compiler::CONTENT_HTML))
			->setAutowired(FALSE);

		foreach ($config['macros'] as $macro) {
			if (strpos($macro, '::') === FALSE && class_exists($macro)) {
				$macro .= '::install';
			} else {
				Nette\Utils\Validators::assert($macro, 'callable');
			}
			$latte->addSetup('?->onCompile[] = function($engine) { ' . $macro . '($engine->getCompiler()); }', array('@self'));
			$latteFactory->addSetup('?->onCompile[] = function($engine) { ' . $macro . '($engine->getCompiler()); }', array('@self'));
		}

		if (class_exists('Nette\Templating\FileTemplate')) {
			$container->addDefinition('nette.template')
				->setFactory('Nette\Templating\FileTemplate')
				->addSetup('::trigger_error', array('Service nette.template is deprecated.', E_USER_DEPRECATED))
				->addSetup('registerFilter', array(new Nette\DI\Statement(array($latteFactory, 'create'))))
				->addSetup('registerHelperLoader', array('Nette\Templating\Helpers::loader'))
				->setAutowired(FALSE);
		}
	}


	public function afterCompile(Nette\PhpGenerator\ClassType $class)
	{
		if ($this->xhtml) {
			$class->methods['initialize']->addBody('Nette\Utils\Html::$xhtml = TRUE;');
		}
	}


	private function validate(array $config, array $expected, $name)
	{
		if ($extra = array_diff_key($config, $expected)) {
			$extra = implode(", $name.", array_keys($extra));
			throw new Nette\InvalidStateException("Unknown option $name.$extra.");
		}
	}

}
