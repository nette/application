<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Bridges\LatteDI;

use Nette,
	Nette\DI\ContainerBuilder,
	Nette\Utils\Validators,
	Latte;


/**
 * Nette Framework Latte services.
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

	private $config;


	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();

		$config = $this->compiler->getConfig();

		if (isset($config['nette']['xhtml']) || isset($config['nette']['latte'])) { // back compatibility
			if (isset($config['nette']['xhtml'])) {
				$config['nette']['latte']['xhtml'] = $config['nette']['xhtml'];
				trigger_error('nette.xhtml configuration is deprecated, use latte.xhtml instead.', E_USER_DEPRECATED);
			} elseif (isset($config['nette']['latte'])) {
				trigger_error('nette.latte configuration section is deprecated, use latte section instead.', E_USER_DEPRECATED);
			}
			$config = Nette\DI\Config\Helpers::merge($config['nette']['latte'], $this->defaults);
		} else {
			$config = $this->getConfig($this->defaults);
		}

		$this->validate($config, $this->defaults, 'latte');
		$this->config = $config;

		if (class_exists('Latte\Engine')) { // Only setup if Latte is installed
			$this->setupLatte($container, $config);
		}
	}


	private function setupLatte(ContainerBuilder $container, array $config)
	{
		$latteFactory = $container->addDefinition('nette.latteFactory')
			->setClass('Latte\Engine')
			->addSetup('setTempDirectory', array($container->expand('%tempDir%/cache/latte')))
			->addSetup('setAutoRefresh', array($container->parameters['debugMode']))
			->addSetup('setContentType', array($config['xhtml'] ? Latte\Compiler::CONTENT_XHTML : Latte\Compiler::CONTENT_HTML))
			->setImplement('Nette\Bridges\ApplicationLatte\ILatteFactory');

		$container->addDefinition('nette.templateFactory')
			->setClass('Nette\Bridges\ApplicationLatte\TemplateFactory');

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
				Validators::assert($macro, 'callable');
			}
			$latte->addSetup('?->onCompile[] = function($engine) { ' . $macro . '($engine->getCompiler()); }', array('@self'));
			$latteFactory->addSetup('?->onCompile[] = function($engine) { ' . $macro . '($engine->getCompiler()); }', array('@self'));
		}

		if (class_exists('Nette\Templating\FileTemplate')) {
			$container->addDefinition('nette.template')
				->setClass('Nette\Templating\FileTemplate')
				->addSetup('::trigger_error', array('Service nette.template is deprecated.', E_USER_DEPRECATED))
				->addSetup('registerFilter', array(new Nette\DI\Statement(array($latteFactory, 'create'))))
				->addSetup('registerHelperLoader', array('Nette\Templating\Helpers::loader'))
				->setAutowired(FALSE);
		}
	}


	public function afterCompile(Nette\PhpGenerator\ClassType $class)
	{
		$initialize = $class->methods['initialize'];

		if ($this->config['xhtml']) {
			$initialize->addBody('Nette\Utils\Html::$xhtml = ?;', array(TRUE));
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
