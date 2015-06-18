<?php

/**
 * Test: Nette\Application\UI\PresenterComponent::redirect()
 */

use Nette\Http;
use Nette\Application;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class TestPresenter extends Application\UI\Presenter
{
	public $response;

	function actionFoo($val)
	{
	}

	function sendResponse(Application\IResponse $response)
	{
		$this->response = $response;
	}
}


$presenter = new TestPresenter;
$presenter->setParent(NULL, 'test');
$presenter->injectPrimary(
	NULL,
	NULL,
	new Application\Routers\SimpleRouter,
	new Http\Request(new Http\UrlScript('http://localhost')),
	new Http\Response
);


test(function () use ($presenter) {
	$presenter->redirect('foo');
	Assert::type('Nette\Application\Responses\RedirectResponse', $presenter->response);
	Assert::same(302, $presenter->response->getCode());
	Assert::same('http://localhost/?action=foo&presenter=test', $presenter->response->getUrl());
});


test(function () use ($presenter) {
	$presenter->redirect('foo', ['arg' => 1]);
	Assert::type('Nette\Application\Responses\RedirectResponse', $presenter->response);
	Assert::same(302, $presenter->response->getCode());
	Assert::same('http://localhost/?arg=1&action=foo&presenter=test', $presenter->response->getUrl());
});


test(function () use ($presenter) {
	$presenter->redirect('foo', 2);
	Assert::type('Nette\Application\Responses\RedirectResponse', $presenter->response);
	Assert::same(302, $presenter->response->getCode());
	Assert::same('http://localhost/?val=2&action=foo&presenter=test', $presenter->response->getUrl());
});


test(function () use ($presenter) {
	$presenter->redirect(301, 'foo', ['arg' => 1]);
	Assert::type('Nette\Application\Responses\RedirectResponse', $presenter->response);
	Assert::same(301, $presenter->response->getCode());
	Assert::same('http://localhost/?arg=1&action=foo&presenter=test', $presenter->response->getUrl());
});


test(function () use ($presenter) {
	$presenter->redirect(301, 'foo', 2);
	Assert::type('Nette\Application\Responses\RedirectResponse', $presenter->response);
	Assert::same(301, $presenter->response->getCode());
	Assert::same('http://localhost/?val=2&action=foo&presenter=test', $presenter->response->getUrl());
});
