<?php

/**
 * Test: Nette\Application\LinkGenerator basic functions.
 */

namespace {

	require __DIR__ . '/../bootstrap.php';

	class HomepagePresenter extends Nette\Application\UI\Presenter
	{
		function actionDefault($a)
		{}

		function renderDetail($b)
		{}
	}

}


namespace ModuleModule {

	use Nette;

	class MyPresenter implements Nette\Application\IPresenter
	{
		function run(Nette\Application\Request $request)
		{}
	}

}


namespace {

	use Nette\Http,
		Nette\Application\LinkGenerator,
		Nette\Application\PresenterFactory,
		Nette\Application\Routers,
		Tester\Assert;


	$pf = new PresenterFactory;


	test(function() use ($pf) {
		$generator = new LinkGenerator(new Routers\SimpleRouter, new Http\Url('http://nette.org/en/'), $pf);
		Assert::same('http://nette.org/en/?action=default&presenter=Homepage',  $generator->link('Homepage:default'));
		Assert::same('http://nette.org/en/?action=default&presenter=Module%3AMy',  $generator->link('Module:My:default'));
		Assert::same('http://nette.org/en/?presenter=Module%3AMy',  $generator->link('Module:My:'));
		Assert::same('http://nette.org/en/?action=default&presenter=Homepage',  $generator->link('Homepage:'));
		Assert::same('http://nette.org/en/?a=10&action=default&presenter=Homepage',  $generator->link('Homepage:', array(10)));
		Assert::same('http://nette.org/en/?id=20&b=10&action=detail&presenter=Homepage',  $generator->link('Homepage:detail', array(10, 'id' => 20)));
		Assert::same('http://nette.org/en/?action=default&presenter=Homepage#frag:ment',  $generator->link('Homepage:#frag:ment'));
	});


	Assert::exception(function() use ($pf) {
		$generator = new LinkGenerator(new Routers\SimpleRouter, new Http\Url('http://nette.org/en/'), $pf);
		$generator->link('default');
	}, 'Nette\Application\UI\InvalidLinkException', "Invalid link destination 'default'.");


	Assert::exception(function() use ($pf) {
		$generator = new LinkGenerator(new Routers\Route('/', 'Product:'), new Http\Url('http://nette.org/en/'), $pf);
		$generator->link('Homepage:default', array('id' => 10));
	}, 'Nette\Application\UI\InvalidLinkException', 'No route for Homepage:default(id=10)');


	test(function() {
		$generator = new LinkGenerator(new Routers\SimpleRouter, new Http\Url('http://nette.org/en/'));
		Assert::same('http://nette.org/en/?action=default&presenter=Homepage',  $generator->link('Homepage:default'));
		Assert::same('http://nette.org/en/?action=default&presenter=Module%3AMy',  $generator->link('Module:My:default'));
		Assert::same('http://nette.org/en/?presenter=Module%3AMy',  $generator->link('Module:My:'));
		Assert::same('http://nette.org/en/?presenter=Homepage',  $generator->link('Homepage:'));
		Assert::same('http://nette.org/en/?0=10&presenter=Homepage',  $generator->link('Homepage:', array(10)));
		Assert::same('http://nette.org/en/?0=10&id=20&action=detail&presenter=Homepage',  $generator->link('Homepage:detail', array(10, 'id' => 20)));
		Assert::same('http://nette.org/en/?presenter=Homepage#frag:ment',  $generator->link('Homepage:#frag:ment'));
	});

}
