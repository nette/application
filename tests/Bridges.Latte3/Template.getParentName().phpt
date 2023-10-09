<?php

/** @phpVersion 8.0 */

declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}

Tester\Environment::bypassFinals();

$presenter = Mockery::mock(Nette\Application\UI\Presenter::class)
	->shouldReceive('getPresenterIfExists')->andReturnSelf()
	->shouldReceive('getHttpResponse')->andReturn(Mockery::mock(Nette\Http\IResponse::class)->shouldIgnoreMissing())
	->shouldReceive('findLayoutTemplateFile')->andReturn('layout.latte')
	->mock();

$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension($presenter));

$template = $latte->createTemplate('');
$template->render();
Assert::null($template->getParentName());

$template = $latte->createTemplate('{block}...{/block}');
$template->render();
Assert::null($template->getParentName());

$template = $latte->createTemplate('{block name}...{/block}');
Assert::exception(
	fn() => $template->render(),
	LogicException::class, // missing template
);
Assert::same('layout.latte', $template->getParentName());

$template = $latte->createTemplate('{extends "file.latte"} {block name}...{/block}');
Assert::exception(
	fn() => $template->render(),
	LogicException::class, // missing template
);
Assert::same('file.latte', $template->getParentName());

$template = $latte->createTemplate('{extends "file.latte"}');
Assert::exception(
	fn() => $template->render(),
	LogicException::class, // missing template
);
Assert::same('file.latte', $template->getParentName());

$template = $latte->createTemplate(
	'{extends $file} {block name}...{/block}',
	['file' => 'file.latte'],
);
Assert::exception(
	fn() => $template->render(),
	LogicException::class, // missing template
);
Assert::same('file.latte', $template->getParentName());

$template = $latte->createTemplate('{extends none}');
$template->render();
Assert::null($template->getParentName());

$template = $latte->createTemplate('{extends auto} {block name}...{/block}');
Assert::exception(
	fn() => $template->render(),
	LogicException::class, // missing template
);
Assert::same('layout.latte', $template->getParentName());


$latte->setLoader(new Latte\Loaders\StringLoader([
	'main.latte' => '{extends foo.latte}',
	'foo.latte' => '{extends auto}',
	'layout.latte' => 'layout',
]));
Assert::same('layout', $template = $latte->renderToString('main.latte'));
