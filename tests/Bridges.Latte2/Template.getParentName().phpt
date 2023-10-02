<?php

declare(strict_types=1);

use Nette\Bridges\ApplicationLatte\UIMacros;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '>')) {
	Tester\Environment::skip('Test for Latte 2');
}


$presenter = Mockery::mock(Nette\Application\UI\Presenter::class)
	->shouldReceive('findLayoutTemplateFile')->andReturn('layout.latte')
	->mock();

$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addProvider('uiControl', $presenter);
UIMacros::install($latte->getCompiler());

$template = $latte->createTemplate('');
$template->prepare();
Assert::null($template->getParentName());

$template = $latte->createTemplate('{block}...{/block}');
$template->prepare();
Assert::null($template->getParentName());

$template = $latte->createTemplate('{block name}...{/block}');
$template->prepare();
Assert::same('layout.latte', $template->getParentName());

$template = $latte->createTemplate('{extends "file.latte"} {block name}...{/block}');
$template->prepare();
Assert::same('file.latte', $template->getParentName());

$template = $latte->createTemplate('{extends "file.latte"}');
$template->prepare();
Assert::same('file.latte', $template->getParentName());

$template = $latte->createTemplate(
	'{extends $file} {block name}...{/block}',
	['file' => 'file.latte'],
);
$template->prepare();
Assert::same('file.latte', $template->getParentName());

$template = $latte->createTemplate('{extends none}');
$template->prepare();
Assert::null($template->getParentName());

$latte->addProvider('uiPresenter', $presenter);
$template = $latte->createTemplate('{extends auto}');
$template->prepare();
Assert::same('layout.latte', $template->getParentName());
