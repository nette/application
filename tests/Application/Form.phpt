<?php

/**
 * Test: Nette\Application\UI\Form
 */

use Nette\Application\UI,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class TestPresenter extends UI\Presenter
{

}


test(function() {
	$presenter = new TestPresenter;
	$form = new UI\Form($presenter, 'name');
	$form->setMethod($form::GET); // must not throw exception
});


test(function() { // compatibility with 2.0
	$presenter = new TestPresenter;
	$form = new UI\Form;
	$form->setAction('action');
	$presenter['name'] = $form;
	Assert::false(isset($form[TestPresenter::SIGNAL_KEY]));
});
