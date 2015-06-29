<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Bridges\ApplicationDI;

use Nette;
use Nette\Application\UI;


/**
 * Application extension for Nette DI.
 */
class ApplicationExtension extends Nette\DI\CompilerExtension
{
	public $defaults = array(
		'debugger' => TRUE,
		'errorPresenter' => 'Nette:Error',
		'catchExceptions' => NULL,
		'mapping' => NULL,
		'scanDirs' => array(),
		'scanComposer' => NULL,
		'scanFilter' => 'Presenter',
		'silentLinks' => FALSE,
	);

	/** @var bool */
	private $debugMode;

	/** @var int */
	private $invalidLinkMode;

	/** @var string */
	private $tempFile;


	public function __construct($debugMode = FALSE, array $scanDirs = NULL, $tempDir = NULL)
	{
		$this->defaults['scanDirs'] = (array) $scanDirs;
		$this->defaults['scanComposer'] = class_exists('Composer\Autoload\ClassLoader');
		$this->defaults['catchExceptions'] = !$debugMode;
		$this->debugMode = $debugMode;
		$this->tempFile = $tempDir ? $tempDir . '/' . urlencode(__CLASS__) : NULL;
	}


	public function loadConfiguration()
	{
		$config = $this->validateConfig($this->defaults);
		$container = $this->getContainerBuilder();
		$container->addExcludedClasses(array('Nette\Application\UI\Control'));

		$this->invalidLinkMode = $this->debugMode
			? UI\Presenter::INVALID_LINK_TEXTUAL | ($config['silentLinks'] ? 0 : UI\Presenter::INVALID_LINK_WARNING)
			: UI\Presenter::INVALID_LINK_WARNING;

		$application = $container->addDefinition($this->prefix('application'))
			->setClass('Nette\Application\Application')
			->addSetup('$catchExceptions', array($config['catchExceptions']))
			->addSetup('$errorPresenter', array($config['errorPresenter']));

		if ($config['debugger']) {
			$application->addSetup('Nette\Bridges\ApplicationTracy\RoutingPanel::initializePanel');
		}

		$touch = $this->debugMode && $config['scanDirs'] ? $this->tempFile : NULL;
		$presenterFactory = $container->addDefinition($this->prefix('presenterFactory'))
			->setClass('Nette\Application\IPresenterFactory')
			->setFactory('Nette\Application\PresenterFactory', array(new Nette\DI\Statement(
				'Nette\Bridges\ApplicationDI\PresenterFactoryCallback', array(1 => $this->invalidLinkMode, $touch)
			)));

		if ($config['mapping']) {
			$presenterFactory->addSetup('setMapping', array($config['mapping']));
		}

		$container->addDefinition($this->prefix('linkGenerator'))
			->setFactory('Nette\Application\LinkGenerator', array(
				1 => new Nette\DI\Statement('@Nette\Http\IRequest::getUrl'),
			));

		if ($this->name === 'application') {
			$container->addAlias('application', $this->prefix('application'));
			$container->addAlias('nette.presenterFactory', $this->prefix('presenterFactory'));
		}
	}


	public function beforeCompile()
	{
		$container = $this->getContainerBuilder();
		$all = array();

		foreach ($container->findByType('Nette\Application\IPresenter') as $def) {
			$all[$def->getClass()] = $def;
		}

		$counter = 0;
		foreach ($this->findPresenters() as $class) {
			if (empty($all[$class])) {
				$all[$class] = $container->addDefinition($this->prefix(++$counter))->setClass($class);
			}
		}

		foreach ($all as $def) {
			$def->setInject(TRUE)->setAutowired(FALSE)->addTag('nette.presenter', $def->getClass());
			if (is_subclass_of($def->getClass(), 'Nette\Application\UI\Presenter')) {
				$def->addSetup('$invalidLinkMode', array($this->invalidLinkMode));
			}
		}
	}


	/** @return string[] */
	private function findPresenters()
	{
		$config = $this->getConfig();
		$classes = array();

		if ($config['scanDirs']) {
			if (!class_exists('Nette\Loaders\RobotLoader')) {
				throw new Nette\NotSupportedException("RobotLoader is required to find presenters, install package `nette/robot-loader` or disable option {$this->prefix('scanDirs')}: false");
			}
			$robot = new Nette\Loaders\RobotLoader;
			$robot->setCacheStorage(new Nette\Caching\Storages\DevNullStorage);
			$robot->addDirectory($config['scanDirs']);
			$robot->acceptFiles = '*' . $config['scanFilter'] . '*.php';
			$robot->rebuild();
			$classes = array_keys($robot->getIndexedClasses());
			$this->getContainerBuilder()->addDependency($this->tempFile);
		}

		if ($config['scanComposer']) {
			$rc = new \ReflectionClass('Composer\Autoload\ClassLoader');
			$classFile = dirname($rc->getFileName()) . '/autoload_classmap.php';
			if (is_file($classFile)) {
				$this->getContainerBuilder()->addDependency($classFile);
				$classes = array_merge($classes, array_keys(call_user_func(function ($path) {
					return require $path;
				}, $classFile)));
			}
		}

		$presenters = array();
		foreach (array_unique($classes) as $class) {
			if (strpos($class, $config['scanFilter']) !== FALSE && class_exists($class)
				&& ($rc = new \ReflectionClass($class)) && $rc->implementsInterface('Nette\Application\IPresenter')
				&& !$rc->isAbstract()
			) {
				$presenters[] = $rc->getName();
			}
		}
		return $presenters;
	}

}
