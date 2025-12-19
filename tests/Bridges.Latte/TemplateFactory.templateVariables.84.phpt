<?php

/**
 * Test: TemplateFactory in Bridge injects annotated variables into Template
 * @phpVersion 8.4
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


class HookPresenter extends Nette\Application\UI\Presenter
{
	#[TemplateVariable]
	public int $virtual {
		get => 1;
		set => $value;
	}
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


test('virtual uninitialized variable', function () {
	$presenter = new HookPresenter;
	injectPresenter($presenter);

	$template = $presenter->template;
	$template->setFile('');
	$presenter->run(new Nette\Application\Request(''));

	Assert::same(1, $template->virtual);
});
