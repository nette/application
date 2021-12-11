<?php

/**
 * Test: Presenter::isModuleCurrent.
 */

declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class TestPresenter extends Nette\Application\UI\Presenter
{
}


test('', function () {
	$presenter = new TestPresenter;
	$presenter->setParent(null, 'Test');

	Assert::true($presenter->isModuleCurrent(''));
	Assert::false($presenter->isModuleCurrent('Test'));
	Assert::false($presenter->isModuleCurrent(':Test'));
});


test('', function () {
	$presenter = new TestPresenter;
	$presenter->setParent(null, 'First:Second:Third:Test');

	Assert::false($presenter->isModuleCurrent('First:Second:Third:Test'));

	Assert::true($presenter->isModuleCurrent('First:Second:Third'));
	Assert::true($presenter->isModuleCurrent('First:Second'));
	Assert::true($presenter->isModuleCurrent('First'));
	Assert::true($presenter->isModuleCurrent(''));

	Assert::true($presenter->isModuleCurrent(':First:Second:Third'));
	Assert::true($presenter->isModuleCurrent(':First:Second'));
	Assert::true($presenter->isModuleCurrent(':First'));
	Assert::true($presenter->isModuleCurrent(':'));

	Assert::false($presenter->isModuleCurrent('First:Second:Other'));
	Assert::false($presenter->isModuleCurrent('First:Other'));
	Assert::false($presenter->isModuleCurrent('First:Second:T'));
	Assert::false($presenter->isModuleCurrent('First:S'));
});
