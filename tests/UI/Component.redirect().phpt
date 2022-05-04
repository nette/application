<?php

/**
 * Test: Nette\Application\UI\Component::redirect()
 */

declare(strict_types=1);

use Nette\Application;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class TestPresenter extends Application\UI\Presenter
{
	public $response;


	public function actionFoo($val)
	{
	}


	public function sendResponse(Application\Response $response): void
	{
		$this->response = $response;
	}
}


$presenter = new TestPresenter;
$presenter->setParent(null, 'test');
$presenter->injectPrimary(
	new Http\Request(new Http\UrlScript('http://localhost')),
	new Http\Response,
	null,
	new Application\Routers\SimpleRouter,
);


test('', function () use ($presenter) {
	$presenter->redirect('foo');
	Assert::type(Nette\Application\Responses\RedirectResponse::class, $presenter->response);
	Assert::same(302, $presenter->response->getCode());
	Assert::same('http://localhost/?action=foo&presenter=test', $presenter->response->getUrl());
});


test('', function () use ($presenter) {
	$presenter->redirect('foo', ['arg' => 1]);
	Assert::type(Nette\Application\Responses\RedirectResponse::class, $presenter->response);
	Assert::same(302, $presenter->response->getCode());
	Assert::same('http://localhost/?arg=1&action=foo&presenter=test', $presenter->response->getUrl());
});


test('', function () use ($presenter) {
	$presenter->redirect('foo', 2);
	Assert::type(Nette\Application\Responses\RedirectResponse::class, $presenter->response);
	Assert::same(302, $presenter->response->getCode());
	Assert::same('http://localhost/?val=2&action=foo&presenter=test', $presenter->response->getUrl());
});


test('', function () use ($presenter) {
	$presenter->redirectPermanent('foo', 2);
	Assert::type(Nette\Application\Responses\RedirectResponse::class, $presenter->response);
	Assert::same(301, $presenter->response->getCode());
	Assert::same('http://localhost/?val=2&action=foo&presenter=test', $presenter->response->getUrl());
});


test('', function () use ($presenter) {
	$presenter->redirectPermanent('foo', ['arg' => 1]);
	Assert::type(Nette\Application\Responses\RedirectResponse::class, $presenter->response);
	Assert::same(301, $presenter->response->getCode());
	Assert::same('http://localhost/?arg=1&action=foo&presenter=test', $presenter->response->getUrl());
});
