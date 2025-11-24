<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Bridges\ApplicationDI;

use Latte;
use Nette;
use Nette\Bridges\ApplicationLatte;
use Nette\DI\Definitions\Statement;
use Nette\Schema\Expect;
use Tracy;
use function class_exists, is_string;


/**
 * Latte extension for Nette DI.
 *
 * @property object{
 *     debugger: bool|'all'|null,
 *     macros: array<string>,
 *     extensions: array<string|Nette\DI\Definitions\Statement>,
 *     templateClass: string|null,
 *     strictTypes: bool,
 *     strictParsing: bool,
 *     scopedLoopVariables: bool,
 *     dedent: bool,
 *     phpLinter: string|null,
 *     locale: string|null,
 * } $config
 */
final class LatteExtension extends Nette\DI\CompilerExtension
{
	public function __construct(
		private readonly string $tempDir,
		private readonly bool $debugMode = false,
	) {
	}


	public function getConfigSchema(): Nette\Schema\Schema
	{
		return Expect::structure([
			'debugger' => Expect::anyOf(true, false, 'all'),
			'extensions' => Expect::arrayOf('string|Nette\DI\Definitions\Statement'),
			'templateClass' => Expect::string(),
			'strictTypes' => Expect::bool(false),
			'strictParsing' => Expect::bool(false),
			'scopedLoopVariables' => Expect::bool(false),
			'dedent' => Expect::bool(false),
			'phpLinter' => Expect::string(),
			'locale' => Expect::string(),
		]);
	}


	public function loadConfiguration(): void
	{
		if (!class_exists(Latte\Engine::class)) {
			return;
		}

		$config = $this->config;
		$builder = $this->getContainerBuilder();

		$builder->addFactoryDefinition($this->prefix('latteFactory'))
			->setImplement(ApplicationLatte\LatteFactory::class)
			->getResultDefinition()
				->setFactory(Latte\Engine::class)
				->addSetup('setCacheDirectory', [$this->tempDir])
				->addSetup('setAutoRefresh', [$this->debugMode])
				->addSetup('setFeature', [Latte\Feature::StrictTypes, $config->strictTypes])
				->addSetup('setFeature', [Latte\Feature::StrictParsing, $config->strictParsing])
				->addSetup('setFeature', [Latte\Feature::ScopedLoopVariables, $config->scopedLoopVariables])
				->addSetup('setFeature', [Latte\Feature::Dedent, $config->dedent])
				->addSetup('enablePhpLinter', [$config->phpLinter])
				->addSetup('setLocale', [$config->locale])
				->addSetup('?', [$builder::literal('func_num_args() && $service->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension(func_get_arg(0)))')]);

		if ($builder->getByType(Nette\Caching\Storage::class)) {
			$this->addExtension(new Statement(Nette\Bridges\CacheLatte\CacheExtension::class));
		}
		if (class_exists(Nette\Bridges\FormsLatte\FormsExtension::class)) {
			$this->addExtension(new Statement(Nette\Bridges\FormsLatte\FormsExtension::class));
		}

		foreach ($config->extensions as $extension) {
			if ($extension === Latte\Essential\TranslatorExtension::class) {
				$extension = new Statement($extension, [new Nette\DI\Definitions\Reference(Nette\Localization\Translator::class)]);
			}
			$this->addExtension($extension);
		}

		$builder->addDefinition($this->prefix('templateFactory'))
			->setFactory(ApplicationLatte\TemplateFactory::class)
			->setArguments(['templateClass' => $config->templateClass]);

		if ($this->name === 'latte') {
			$builder->addAlias('nette.latteFactory', $this->prefix('latteFactory'));
			$builder->addAlias('nette.templateFactory', $this->prefix('templateFactory'));
		}
	}


	public function beforeCompile(): void
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


	public static function initLattePanel(
		Nette\Application\UI\TemplateFactory $factory,
		Tracy\Bar $bar,
		bool $all = false,
	): void
	{
		if (!$factory instanceof ApplicationLatte\TemplateFactory) {
			return;
		}

		$factory->onCreate[] = function (ApplicationLatte\Template $template) use ($all) {
			$control = $template->getLatte()->getProviders()['uiControl'] ?? null;
			if ($all || $control instanceof Nette\Application\UI\Presenter) {
				$name = $all && $control ? (new \ReflectionObject($control))->getShortName() : '';
				$template->getLatte()->addExtension(new Latte\Bridges\Tracy\TracyExtension($name));
			}
		};
	}


	public function addExtension(Statement|string $extension): void
	{
		$extension = is_string($extension)
			? new Statement($extension)
			: $extension;

		$builder = $this->getContainerBuilder();
		$builder->getDefinition($this->prefix('latteFactory'))
			->getResultDefinition()
			->addSetup('addExtension', [$extension]);
	}
}
