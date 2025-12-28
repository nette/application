<?php

/**
 * Test: Nette\Application\UI\Presenter::canonicalize()
 */

declare(strict_types=1);

use Nette\Application;
use Nette\Application\Attributes\Persistent;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/functions.php';


class CanonicalPresenter extends Application\UI\Presenter
{
	#[Persistent]
	public int $id = 0;


	public function actionDefault(): void
	{
	}


	public function renderDefault(): void
	{
		$this->terminate();
	}
}


test('autoCanonicalize redirects to remove default persistent parameter', function () {
	$presenter = new CanonicalPresenter;
	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript('http://localhost/index.php?id=0&presenter=Canonical&action=default')),
		new Http\Response,
		new Application\PresenterFactory,
		new Application\Routers\SimpleRouter,
	);
	$presenter->autoCanonicalize = true;

	$response = $presenter->run(new Application\Request('Canonical', 'GET', [
		'action' => 'default',
		'id' => 0,
	]));

	Assert::type(Application\Responses\RedirectResponse::class, $response);
	Assert::notContains('id=0', $response->getUrl());
});


test('autoCanonicalize disabled does not redirect', function () {
	$presenter = new CanonicalPresenter;
	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript('http://localhost/index.php?extra=param&id=5&presenter=Canonical&action=default')),
		new Http\Response,
		new Application\PresenterFactory,
		new Application\Routers\SimpleRouter,
	);
	$presenter->autoCanonicalize = false;

	$response = $presenter->run(new Application\Request('Canonical', 'GET', [
		'action' => 'default',
		'id' => 5,
	]));

	Assert::type(Application\Responses\VoidResponse::class, $response);
});


test('canonicalize() does not redirect on AJAX request', function () {
	$presenter = createPresenter(CanonicalPresenter::class, headers: ['X-Requested-With' => 'XMLHttpRequest']);
	$presenter->autoCanonicalize = true;

	$response = $presenter->run(new Application\Request('Canonical', 'GET', [
		'action' => 'default',
		'id' => 5,
	]));

	Assert::type(Application\Responses\VoidResponse::class, $response);
});


test('canonicalize() does not redirect on POST request', function () {
	$presenter = createPresenter(CanonicalPresenter::class, post: ['data' => 'value']);
	$presenter->autoCanonicalize = true;

	$response = $presenter->run(new Application\Request('Canonical', 'POST', [
		'action' => 'default',
		'id' => 5,
	]));

	Assert::type(Application\Responses\VoidResponse::class, $response);
});


class ManualCanonicalPresenter extends Application\UI\Presenter
{
	public function actionDefault(): void
	{
		$this->canonicalize();
	}


	public function renderDefault(): void
	{
		$this->terminate();
	}
}


test('manual canonicalize call works', function () {
	$presenter = new ManualCanonicalPresenter;
	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript('http://localhost/index.php?extra=param&presenter=ManualCanonical&action=default')),
		new Http\Response,
		new Application\PresenterFactory,
		new Application\Routers\SimpleRouter,
	);
	$presenter->autoCanonicalize = false;

	$response = $presenter->run(new Application\Request('ManualCanonical', 'GET', [
		'action' => 'default',
	]));

	Assert::type(Application\Responses\RedirectResponse::class, $response);
});


class VaryingPresenter extends Application\UI\Presenter
{
	public function actionDefault(): void
	{
	}


	public function renderDefault(): void
	{
		$this->terminate();
	}
}


test('canonicalize() uses 301 for non-varying requests', function () {
	$presenter = new VaryingPresenter;
	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript('http://localhost/index.php?extra=param&presenter=Varying&action=default')),
		new Http\Response,
		new Application\PresenterFactory,
		new Application\Routers\SimpleRouter,
	);
	$presenter->autoCanonicalize = true;

	$response = $presenter->run(new Application\Request('Varying', 'GET', [
		'action' => 'default',
	]));

	Assert::type(Application\Responses\RedirectResponse::class, $response);
	Assert::same(Http\IResponse::S301_MovedPermanently, $response->getCode());
});


test('canonicalize() uses 302 for varying requests', function () {
	$presenter = new VaryingPresenter;
	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript('http://localhost/index.php?extra=param&presenter=Varying&action=default')),
		new Http\Response,
		new Application\PresenterFactory,
		new Application\Routers\SimpleRouter,
	);
	$presenter->autoCanonicalize = true;

	$request = new Application\Request('Varying', 'GET', ['action' => 'default']);
	$request->setFlag($request::VARYING, true);

	$response = $presenter->run($request);

	Assert::type(Application\Responses\RedirectResponse::class, $response);
	Assert::same(Http\IResponse::S302_Found, $response->getCode());
});


class DestinationCanonicalPresenter extends Application\UI\Presenter
{
	public function actionDefault(): void
	{
		$this->canonicalize('other');
	}


	public function actionOther(): void
	{
	}


	public function renderDefault(): void
	{
		$this->terminate();
	}


	public function renderOther(): void
	{
		$this->terminate();
	}
}


test('canonicalize() with different destination redirects', function () {
	$presenter = new DestinationCanonicalPresenter;
	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript('http://localhost/index.php?presenter=DestinationCanonical&action=default')),
		new Http\Response,
		new Application\PresenterFactory,
		new Application\Routers\SimpleRouter,
	);
	$presenter->autoCanonicalize = false;

	$response = $presenter->run(new Application\Request('DestinationCanonical', 'GET', [
		'action' => 'default',
	]));

	Assert::type(Application\Responses\RedirectResponse::class, $response);
	Assert::contains('action=other', $response->getUrl());
});
