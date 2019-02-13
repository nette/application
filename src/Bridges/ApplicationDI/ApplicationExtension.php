<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationDI;

use Composer\Autoload\ClassLoader;
use Nette;
use Nette\Application\UI;
use Tracy;


/**
 * Application extension for Nette DI.
 */
final class ApplicationExtension extends Nette\DI\CompilerExtension
{
	private $defaults = [
		'debugger' => null,
		'errorPresenter' => 'Nette:Error',
		'catchExceptions' => null,
		'mapping' => null,
		'scanDirs' => [],
		'scanComposer' => null,
		'scanFilter' => 'Presenter',
		'silentLinks' => false,
	];

	/** @var bool */
	private $debugMode;

	/** @var int */
	private $invalidLinkMode;

	/** @var string */
	private $tempFile;


	public function __construct(bool $debugMode = false, array $scanDirs = null, string $tempDir = null)
	{
		$this->defaults['debugger'] = interface_exists(Tracy\IBarPanel::class);
		$this->defaults['scanDirs'] = (array) $scanDirs;
		$this->defaults['scanComposer'] = class_exists(ClassLoader::class);
		$this->defaults['catchExceptions'] = !$debugMode;
		$this->debugMode = $debugMode;
		$this->tempFile = $tempDir ? $tempDir . '/' . urlencode(__CLASS__) : null;
	}


	public function loadConfiguration()
	{
		$config = $this->validateConfig($this->defaults);
		$builder = $this->getContainerBuilder();
		$builder->addExcludedClasses([UI\Presenter::class]);

		$this->invalidLinkMode = $this->debugMode
			? UI\Presenter::INVALID_LINK_TEXTUAL | ($config['silentLinks'] ? 0 : UI\Presenter::INVALID_LINK_WARNING)
			: UI\Presenter::INVALID_LINK_WARNING;

		$application = $builder->addDefinition($this->prefix('application'))
			->setFactory(Nette\Application\Application::class)
			->addSetup('$catchExceptions', [$config['catchExceptions']])
			->addSetup('$errorPresenter', [$config['errorPresenter']])
			->setExported();

		if ($config['debugger']) {
			$application->addSetup('Nette\Bridges\ApplicationTracy\RoutingPanel::initializePanel');
		}

		$touch = $this->debugMode && $config['scanDirs'] ? $this->tempFile : null;
		$presenterFactory = $builder->addDefinition($this->prefix('presenterFactory'))
			->setType(Nette\Application\IPresenterFactory::class)
			->setFactory(Nette\Application\PresenterFactory::class, [new Nette\DI\Definitions\Statement(
				Nette\Bridges\ApplicationDI\PresenterFactoryCallback::class, [1 => $this->invalidLinkMode, $touch]
			)]);

		if ($config['mapping']) {
			$presenterFactory->addSetup('setMapping', [$config['mapping']]);
		}

		$builder->addDefinition($this->prefix('linkGenerator'))
			->setFactory(Nette\Application\LinkGenerator::class, [
				1 => new Nette\DI\Definitions\Statement('@Nette\Http\IRequest::getUrl'),
			])
			->setExported();

		if ($this->name === 'application') {
			$builder->addAlias('application', $this->prefix('application'));
			$builder->addAlias('nette.presenterFactory', $this->prefix('presenterFactory'));
		}
	}


	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
		$all = [];

		foreach ($builder->findByType(Nette\Application\IPresenter::class) as $def) {
			$all[$def->getType()] = $def;
		}

		$counter = 0;
		foreach ($this->findPresenters() as $class) {
			if (empty($all[$class])) {
				$all[$class] = $builder->addDefinition($this->prefix((string) ++$counter))
					->setType($class)
					->setExported();
			}
		}

		foreach ($all as $def) {
			$def->addTag(Nette\DI\Extensions\InjectExtension::TAG_INJECT)
				->setAutowired(false);

			if (is_subclass_of($def->getType(), UI\Presenter::class)) {
				$def->addSetup('$invalidLinkMode', [$this->invalidLinkMode]);
			}
		}
	}


	private function findPresenters(): array
	{
		$config = $this->getConfig();
		$classes = [];

		if ($config['scanDirs']) {
			if (!class_exists(Nette\Loaders\RobotLoader::class)) {
				throw new Nette\NotSupportedException("RobotLoader is required to find presenters, install package `nette/robot-loader` or disable option {$this->prefix('scanDirs')}: false");
			}
			$robot = new Nette\Loaders\RobotLoader;
			$robot->addDirectory($config['scanDirs']);
			$robot->acceptFiles = ['*' . $config['scanFilter'] . '*.php'];
			$robot->rebuild();
			$classes = array_keys($robot->getIndexedClasses());
			$this->getContainerBuilder()->addDependency($this->tempFile);
		}

		if ($config['scanComposer']) {
			$rc = new \ReflectionClass(ClassLoader::class);
			$classFile = dirname($rc->getFileName()) . '/autoload_classmap.php';
			if (is_file($classFile)) {
				$this->getContainerBuilder()->addDependency($classFile);
				$classes = array_merge($classes, array_keys((function ($path) {
					return require $path;
				})($classFile)));
			}
		}

		$presenters = [];
		foreach (array_unique($classes) as $class) {
			if (
				strpos($class, $config['scanFilter']) !== false
				&& class_exists($class)
				&& ($rc = new \ReflectionClass($class))
				&& $rc->implementsInterface(Nette\Application\IPresenter::class)
				&& !$rc->isAbstract()
			) {
				$presenters[] = $rc->getName();
			}
		}
		return $presenters;
	}
}
