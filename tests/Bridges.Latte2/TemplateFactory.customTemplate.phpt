<?php

/**
 * Test: TemplateFactory custom template
 */

declare(strict_types=1);

use Nette\Application\UI;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '>')) {
	Tester\Environment::skip('Test for Latte 2');
}

Tester\Environment::bypassFinals();


class TemplateMock extends Template
{
	private $file = 'ko';


	public function render(?string $file = null, array $params = []): void
	{
		echo strrev($this->file);
	}


	public function setFile(string $file)
	{
		$this->file = $file;
	}


	public function getFile(): string
	{
		return $this->file;
	}
}


test('', function () {
	$latteFactory = Mockery::mock(LatteFactory::class);
	$latteFactory->shouldReceive('create')->andReturn(new Latte\Engine);
	$factory = new TemplateFactory($latteFactory);
	Assert::type(Template::class, $factory->createTemplate());
});

Assert::exception(function () {
	$factory = new TemplateFactory(Mockery::mock(LatteFactory::class), null, null, null, stdClass::class);
}, Nette\InvalidArgumentException::class, 'Class stdClass does not implement Nette\Bridges\ApplicationLatte\Template or it does not exist.');


test('', function () {
	$latteFactory = Mockery::mock(LatteFactory::class);
	$latteFactory->shouldReceive('create')->andReturn(new Latte\Engine);
	$factory = new TemplateFactory($latteFactory, null, null, null, TemplateMock::class);
	$template = $factory->createTemplate();
	Assert::type(TemplateMock::class, $template);
	Assert::type(UI\Template::class, $template);
	ob_start();
	$template->render();
	Assert::same('ok', ob_get_clean());
	$template->setFile('bla');
	ob_start();
	$template->render();
	Assert::same('alb', ob_get_clean());
});
