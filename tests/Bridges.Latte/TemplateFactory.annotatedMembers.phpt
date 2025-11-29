<?php

/**
 * Test: TemplateFactory in Bridge injects annotated members into Template
 */

declare(strict_types=1);

use Latte\Attributes\TemplateFilter;
use Latte\Attributes\TemplateFunction;
use Nette\Application\Attributes\TemplateVariable;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Nette\Http;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}


class TestPresenter extends Nette\Application\UI\Presenter
{
	#[TemplateVariable]
	public $public = 1;


	#[TemplateFilter]
	private function filterPrivate()
	{
		return 2;
	}


	#[TemplateFilter]
	protected function filterProtected()
	{
		return 2;
	}


	#[TemplateFilter]
	public function filterPublic()
	{
		return 2;
	}


	#[TemplateFunction]
	private function functionPrivate()
	{
		return 3;
	}


	#[TemplateFunction]
	protected function functionProtected()
	{
		return 3;
	}


	#[TemplateFunction]
	public function functionPublic()
	{
		return 3;
	}
}


class BadPresenter extends Nette\Application\UI\Presenter
{
	#[TemplateVariable]
	protected $protected = 1;
}


test('', function () {
	$engine = new Latte\Engine;
	$latteFactory = Mockery::mock(LatteFactory::class);
	$latteFactory->shouldReceive('create')->andReturn($engine);
	$httpRequest = new Http\Request(new Http\UrlScript('http://nette.org'));
	$factory = new TemplateFactory($latteFactory, $httpRequest);
	$presenter = new TestPresenter;
	$presenter->injectPrimary($httpRequest, new Http\Response);

	$template = $factory->createTemplate($presenter);

	Assert::same(1, $template->public);

	$latte = $template->getLatte();
	Assert::same(2, $latte->invokeFilter('filterPublic', []));
	Assert::same(2, $latte->invokeFilter('filterProtected', []));
	Assert::same(2, $latte->invokeFilter('filterPrivate', []));

	Assert::same(3, $latte->invokeFunction('functionPublic', []));
	Assert::same(3, $latte->invokeFunction('functionProtected', []));
	Assert::same(3, $latte->invokeFunction('functionPrivate', []));

	$presenter = new BadPresenter;
	$presenter->injectPrimary($httpRequest, new Http\Response);
	Assert::exception(
		fn() => $factory->createTemplate($presenter),
		LogicException::class,
		'Property BadPresenter::$protected must be public to be used as TemplateVariable.',
	);
});
