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

$template = $latte->createTemplate(
	'',
	['_control' => new MockPresenter]
);
Assert::same('layout.latte', $template->getParentName());

$template = $latte->createTemplate(
	'{block}...{/block}',
	['_control' => new MockPresenter]
);
Assert::same('layout.latte', $template->getParentName());

$template = $latte->createTemplate(
	'{block name}...{/block}',
	['_control' => new MockPresenter]
);
Assert::same('layout.latte', $template->getParentName());

$template = $latte->createTemplate(
	'{extends "file.latte"} {block name}...{/block}',
	['_control' => new MockPresenter]
);
Assert::same('file.latte', $template->getParentName());

$template = $latte->createTemplate(
	'{extends "file.latte"}',
	['_control' => new MockPresenter]
);
Assert::same('file.latte', $template->getParentName());

$template = $latte->createTemplate(
	'{extends $file} {block name}...{/block}',
	['_control' => new MockPresenter, 'file' => 'file.latte']
);
Assert::same('file.latte', $template->getParentName());

$template = $latte->createTemplate(
	'{extends none}',
	['_control' => new MockPresenter]
);
Assert::null($template->getParentName());

$template = $latte->createTemplate(
	'{extends auto}',
	['_presenter' => new MockPresenter]
);
Assert::same('layout.latte', $template->getParentName());
