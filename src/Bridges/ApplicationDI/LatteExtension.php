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
			'debugger' => Expect::anyOf(true, false, 'all'),
			'xhtml' => Expect::bool(false)->deprecated(),
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


	public static function initLattePanel(ApplicationLatte\TemplateFactory $factory, Tracy\Bar $bar, bool $all = false)
	{
		$factory->onCreate[] = function (ApplicationLatte\Template $template) use ($bar, $all) {
			$control = $template->control ?? null;
			if ($all || $control instanceof Nette\Application\UI\Presenter) {
				$bar->addPanel(new Latte\Bridges\Tracy\LattePanel(
					$template->getLatte(),
					$all && $control ? (new \ReflectionObject($control))->getShortName() : ''
				));
			}
		};
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
