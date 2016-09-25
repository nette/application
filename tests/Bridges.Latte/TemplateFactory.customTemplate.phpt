<?php

/**
 * Test: TemplateFactory custom template
 */

use Nette\Application\UI;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class LatteFactoryMock implements Nette\Bridges\ApplicationLatte\ILatteFactory
{
	private $engine;

	public function __construct(Latte\Engine $engine)
	{
		$this->engine = $engine;
	}

	public function create()
	{
		return $this->engine;
	}
}

class TemplateMockWithoutImplement
{

}

class TemplateMock extends Nette\Bridges\ApplicationLatte\Template
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
	$factory = new TemplateFactory(new LatteFactoryMock(new Latte\Engine));
	Assert::type(Template::class, $factory->createTemplate());
});

Assert::exception(function () {
	$factory = new TemplateFactory(new LatteFactoryMock(new Latte\Engine), NULL, NULL, NULL, TemplateMockWithoutImplement::class);
}, \Nette\InvalidArgumentException::class, 'Class TemplateMockWithoutImplement does not extend Nette\Bridges\ApplicationLatte\Template or it does not exist.');


test(function () {
	$factory = new TemplateFactory(new LatteFactoryMock(new Latte\Engine), NULL, NULL, NULL, TemplateMock::class);
	$template = $factory->createTemplate();
	Assert::type(TemplateMock::class, $template);
	Assert::type(UI\ITemplate::class, $template);
	Assert::same([], $template->flashes);
	Assert::same('ok', $template->render());
	$template->setFile('bla');
	Assert::same('alb', $template->render());
});
