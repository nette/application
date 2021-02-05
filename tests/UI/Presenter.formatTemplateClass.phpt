<?php

/**
 * Test: Presenter::formatTemplateClass.
 */

declare(strict_types=1);

use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class APresenter extends Nette\Application\UI\Presenter
{
}

class BPresenter extends Nette\Application\UI\Presenter
{
}

class BTemplate
{
}

class CPresenter extends Nette\Application\UI\Presenter
{
}

class CTemplate implements Nette\Application\UI\Template
{
	public function render(): void
	{
	}


	public function setFile(string $file)
	{
	}


	public function getFile(): ?string
	{
	}
}

class CBarTemplate extends CTemplate
{
}

test('without template', function () {
	$presenter = new APresenter;
	Assert::null($presenter->formatTemplateClass());
});


test('with class', function () {
	Assert::error(function () {
		$presenter = new BPresenter;
		Assert::null($presenter->formatTemplateClass());
	}, E_USER_NOTICE, '%a% BTemplate was found but does not implement%a%');
});


test('with template', function () {
	$presenter = new CPresenter;
	Assert::same(CTemplate::class, $presenter->formatTemplateClass());
});


test('with action template', function () {
	$presenter = new CPresenter;
	$presenter->changeAction('foo');
	Assert::same(CTemplate::class, $presenter->formatTemplateClass());

	$presenter->changeAction('bar');
	Assert::same(CBarTemplate::class, $presenter->formatTemplateClass());
});
