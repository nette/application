<?php

/**
 * Test: Control::formatTemplateClass.
 */

declare(strict_types=1);

use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class AControl extends Nette\Application\UI\Control
{
}

class BControl extends Nette\Application\UI\Control
{
}

class BTemplate
{
}

class CControl extends Nette\Application\UI\Control
{
}

class CTemplate implements Nette\Application\UI\Template
{
	public function render(): void
	{
	}


	public function setFile(string $file): static
	{
	}


	public function getFile(): ?string
	{
	}
}


test('no associated template class', function () {
	$control = new AControl;
	Assert::null($control->formatTemplateClass());
});


test('template class missing interface implementation', function () {
	Assert::error(function () {
		$control = new BControl;
		Assert::null($control->formatTemplateClass());
	}, E_USER_NOTICE, '%a% BTemplate was found but does not implement%a%');
});


test('valid template class implementation', function () {
	$control = new CControl;
	Assert::same(CTemplate::class, $control->formatTemplateClass());
});
