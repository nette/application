<?php

/**
 * Test: TemplateFactory in Bridge injects annotated variables into Template
 */

declare(strict_types=1);

use Nette\Application\Attributes\TemplateVariable;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Nette\Http;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class TestPresenter extends Nette\Application\UI\Presenter
{
	#[TemplateVariable]
	public $var1 = 1;

	#[TemplateVariable]
	public $var2 = 1;

	#[TemplateVariable]
	public int $var3;
}


class BadPresenter extends Nette\Application\UI\Presenter
{
	#[TemplateVariable]
	protected $protected = 1;
}


function injectPresenter(Nette\Application\UI\Presenter $presenter)
{
	$engine = new Latte\Engine;
	$engine->setLoader(new Latte\Loaders\StringLoader);
	$latteFactory = Mockery::mock(LatteFactory::class);
	$latteFactory->shouldReceive('create')->andReturn($engine);
	$httpRequest = new Http\Request(new Http\UrlScript('http://nette.org'));
	$factory = new TemplateFactory($latteFactory, $httpRequest);
	$presenter->injectPrimary($httpRequest, new Http\Response, templateFactory: $factory);
}


test('public variable', function () {
	$presenter = new TestPresenter;
	injectPresenter($presenter);

	$template = $presenter->template;
	$template->setFile('');
	Assert::false(isset($template->var1));
	$template->var2 = 'myvalue';

	$presenter->run(new Nette\Application\Request(''));

	Assert::same(1, $template->var1);
	Assert::same('myvalue', $template->var2);
	Assert::false(isset($template->var3));
});


test('non-public variable', function () {
	$presenter = new BadPresenter;
	injectPresenter($presenter);

	Assert::exception(
		fn() => $presenter->run(new Nette\Application\Request('')),
		LogicException::class,
		'Property BadPresenter::$protected must be public to be used as TemplateVariable.',
	);
});
