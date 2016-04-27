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
UIMacros::install($latte->getCompiler());

$template = $latte->createTemplate('');
$template->params['_control'] = new MockPresenter;
Assert::same('layout.latte', $template->getParentName());

$template = $latte->createTemplate('{block}...{/block}');
$template->params['_control'] = new MockPresenter;
Assert::same('layout.latte', $template->getParentName());

$template = $latte->createTemplate('{block name}...{/block}');
$template->params['_control'] = new MockPresenter;
Assert::same('layout.latte', $template->getParentName());

$template = $latte->createTemplate('{extends "file.latte"} {block name}...{/block}');
$template->params['_control'] = new MockPresenter;
Assert::same('file.latte', $template->getParentName());

$template = $latte->createTemplate('{extends "file.latte"}');
$template->params['_control'] = new MockPresenter;
Assert::same('file.latte', $template->getParentName());

$template = $latte->createTemplate('{extends $file} {block name}...{/block}');
$template->params['_control'] = new MockPresenter;
$template->params['file'] = 'file.latte';
Assert::same('file.latte', $template->getParentName());

$template = $latte->createTemplate('{extends none}');
$template->params['_control'] = new MockPresenter;
Assert::null($template->getParentName());

$template = $latte->createTemplate('{extends auto}');
$template->params['_presenter'] = new MockPresenter;
Assert::same('layout.latte', $template->getParentName());
