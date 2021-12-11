<?php

/**
 * Test: Nette\Application\UI\Presenter::getPersistentComponents
 */

declare(strict_types=1);

use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Presenter;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class OnePresenter extends Presenter
{
}


/**
 * @persistent(a, b)
 */
class TwoPresenter extends Presenter
{
}


#[Persistent('a', 'b')]
class ThreePresenter extends Presenter
{
}


Assert::same([], OnePresenter::getPersistentComponents());

Assert::same(['a', 'b'], TwoPresenter::getPersistentComponents());

if (PHP_VERSION_ID >= 80000) {
	Assert::same(['a', 'b'], ThreePresenter::getPersistentComponents());
}
