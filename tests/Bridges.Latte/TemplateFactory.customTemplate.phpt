<?php

/**
 * Test: TemplateFactory custom template
 */

declare(strict_types=1);

use Nette\Application\UI;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class TemplateMock extends Template
{
	private $file = 'ko';

	public function render()
	{
		return strrev($this->file);
	}

	public function setFile($file)
	{
		$this->file = $file;
	}

	public function getFile()
	{
		return $this->file;
	}
}


test(function () {
	$latteFactory = Mockery::mock(ILatteFactory::class);
	$latteFactory->shouldReceive('create')->andReturn(new Latte\Engine);
	$factory = new TemplateFactory($latteFactory);
	Assert::type(Template::class, $factory->createTemplate());
});

Assert::exception(function () {
	$factory = new TemplateFactory(Mockery::mock(ILatteFactory::class), NULL, NULL, NULL, stdClass::class);
}, \Nette\InvalidArgumentException::class, 'Class stdClass does not extend Nette\Bridges\ApplicationLatte\Template or it does not exist.');


test(function () {
	$latteFactory = Mockery::mock(ILatteFactory::class);
	$latteFactory->shouldReceive('create')->andReturn(new Latte\Engine);
	$factory = new TemplateFactory($latteFactory, NULL, NULL, NULL, TemplateMock::class);
	$template = $factory->createTemplate();
	Assert::type(TemplateMock::class, $template);
	Assert::type(UI\ITemplate::class, $template);
	Assert::same([], $template->flashes);
	Assert::same('ok', $template->render());
	$template->setFile('bla');
	Assert::same('alb', $template->render());
});
