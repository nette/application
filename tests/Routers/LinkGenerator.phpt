<?php

/**
 * Test: Nette\Application\LinkGenerator basic functions.
 */

declare(strict_types=1);

namespace {

	require __DIR__ . '/../bootstrap.php';

	class HomepagePresenter extends Nette\Application\UI\Presenter
	{
		public function actionDefault($a)
		{
		}


		public function renderDetail($b)
		{
		}
	}

}

namespace ModuleModule {

	use Nette;

	class MyPresenter implements Nette\Application\IPresenter
	{
		public function run(Nette\Application\Request $request): Nette\Application\Response
		{
		}
	}

}

namespace {

	use Nette\Application\LinkGenerator;
	use Nette\Application\PresenterFactory;
	use Nette\Application\Routers;
	use Nette\Http;
	use Tester\Assert;


	$pf = new PresenterFactory;


	test('', function () use ($pf) {
		$generator = new LinkGenerator(new Routers\SimpleRouter, new Http\UrlScript('http://nette.org/en/'), $pf);
		Assert::same('http://nette.org/en/?action=default&presenter=Homepage', $generator->link('Homepage:default'));
		Assert::same('http://nette.org/en/?action=default&presenter=Module%3AMy', $generator->link('Module:My:default'));
		Assert::same('http://nette.org/en/?presenter=Module%3AMy', $generator->link('Module:My:'));
		Assert::same('http://nette.org/en/?action=default&presenter=Homepage', $generator->link('Homepage:'));
		Assert::same('http://nette.org/en/?a=10&action=default&presenter=Homepage', $generator->link('Homepage:', [10]));
		Assert::same('http://nette.org/en/?id=20&b=10&action=detail&presenter=Homepage', $generator->link('Homepage:detail', [10, 'id' => 20]));
		Assert::same('http://nette.org/en/?action=default&presenter=Homepage#frag:ment', $generator->link('Homepage:#frag:ment'));
		Assert::same('http://nette.org/en/?id=10&action=missing&presenter=Homepage', $generator->link('Homepage:missing', ['id' => 10]));
	});


	Assert::exception(function () use ($pf) {
		$generator = new LinkGenerator(new Routers\SimpleRouter, new Http\UrlScript('http://nette.org/en/'), $pf);
		$generator->link('default');
	}, Nette\Application\UI\InvalidLinkException::class, "Invalid link destination 'default'.");


	Assert::exception(function () use ($pf) {
		$generator = new LinkGenerator(new Routers\Route('/', 'Product:'), new Http\UrlScript('http://nette.org/en/'), $pf);
		$generator->link('Homepage:default', ['id' => 10]);
	}, Nette\Application\UI\InvalidLinkException::class, 'No route for Homepage:default(id=10)');


	Assert::exception(function () use ($pf) {
		$generator = new LinkGenerator(new Routers\Route('/', 'Homepage:'), new Http\UrlScript('http://nette.org/en/'), $pf);
		$generator->link('Homepage:missing', [10]);
	}, Nette\Application\UI\InvalidLinkException::class, "Unable to pass parameters to action 'Homepage:missing', missing corresponding method.");


	test('', function () {
		$generator = new LinkGenerator(new Routers\SimpleRouter, new Http\UrlScript('http://nette.org/en/'));
		Assert::same('http://nette.org/en/?action=default&presenter=Homepage', $generator->link('Homepage:default'));
		Assert::same('http://nette.org/en/?action=default&presenter=Module%3AMy', $generator->link('Module:My:default'));
		Assert::same('http://nette.org/en/?presenter=Module%3AMy', $generator->link('Module:My:'));
		Assert::same('http://nette.org/en/?presenter=Homepage', $generator->link('Homepage:'));
		Assert::same('http://nette.org/en/?0=10&presenter=Homepage', $generator->link('Homepage:', [10]));
		Assert::same('http://nette.org/en/?0=10&id=20&action=detail&presenter=Homepage', $generator->link('Homepage:detail', [10, 'id' => 20]));
		Assert::same('http://nette.org/en/?presenter=Homepage#frag:ment', $generator->link('Homepage:#frag:ment'));
		Assert::same('http://nette.org/en/?action=default&presenter=Homepage', $generator->link('Homepage:default'));
	});


	test('', function () {
		$generator = new LinkGenerator(new Routers\SimpleRouter, new Http\UrlScript('http://nette.org/en/'));
		$generator2 = $generator->withReferenceUrl('http://nette.org/cs/');
		Assert::same('http://nette.org/en/?action=default&presenter=Homepage', $generator->link('Homepage:default'));
		Assert::same('http://nette.org/cs/?action=default&presenter=Homepage', $generator2->link('Homepage:default'));
	});
}
