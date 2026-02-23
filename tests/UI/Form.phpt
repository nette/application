<?php declare(strict_types=1);

/**
 * Test: Nette\Application\UI\Form
 */

use Nette\Application\UI;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class TestPresenter extends UI\Presenter
{
}


test('form method setup validation', function () {
	$presenter = new TestPresenter;
	$form = new UI\Form($presenter, 'name');
	$form->setMethod($form::Get); // must not throw exception
});


test('form action assignment check', function () {
	$presenter = new TestPresenter;
	$form = new UI\Form;
	$form->setAction('action');
	$presenter['name'] = $form;
	Assert::false(isset($form[TestPresenter::SignalKey]));
});
