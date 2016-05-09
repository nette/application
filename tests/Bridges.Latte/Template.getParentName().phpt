<?php

use Nette\Bridges\ApplicationLatte\UIMacros;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class MockPresenter extends Nette\Application\UI\Presenter
{
	function findLayoutTemplateFile()
	{
		return 'layout.latte';
	}
}


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addProvider('uiControl', new MockPresenter);
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
	['file' => 'file.latte']
);
$template->prepare();
Assert::same('file.latte', $template->getParentName());

$template = $latte->createTemplate('{extends none}');
$template->prepare();
Assert::null($template->getParentName());

$latte->addProvider('uiPresenter', new MockPresenter);
$template = $latte->createTemplate('{extends auto}');
$template->prepare();
Assert::same('layout.latte', $template->getParentName());
