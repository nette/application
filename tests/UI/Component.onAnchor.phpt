<?php

declare(strict_types=1);

use Nette\Application;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class TestPresenter extends Application\UI\Presenter
{
}

class TestControl extends Application\UI\Control
{
}

$presenter = new TestPresenter;
$control1 = new TestControl;
$control2 = new TestControl;
$control2->onAnchor[] = function ($control) use ($presenter, $control2) {
	Assert::same($control2, $control);
	Assert::same($presenter, $control->getParent()->getParent());
};

$control1['a'] = $control2;
$presenter['a'] = $control1;
