<?php

declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}

Tester\Environment::bypassFinals();

$latte = new Latte\Engine;

$latteFactory = Mockery::mock(Nette\Bridges\ApplicationLatte\LatteFactory::class);
$latteFactory->shouldReceive('create')->andReturn($latte);

$presenter = Mockery::mock(Nette\Application\UI\Presenter::class);
$presenter->shouldReceive('getPresenterIfExists')->andReturn($presenter);
$presenter->shouldReceive('getHttpResponse')->andReturn((Mockery::mock(Nette\Http\IResponse::class))->shouldIgnoreMissing());
$presenter->shouldIgnoreMissing();

$factory = new Nette\Bridges\ApplicationLatte\TemplateFactory($latteFactory);
$factory->createTemplate($presenter);

$latte->setLoader(new Latte\Loaders\StringLoader);

Assert::matchFile(
	__DIR__ . '/expected/ifCurrent.php',
	@$latte->compile( // is deprecated
		<<<'XX'
			{ifCurrent}empty{/ifCurrent}

			{ifCurrent default}default{/ifCurrent}
			XX,
	),
);
