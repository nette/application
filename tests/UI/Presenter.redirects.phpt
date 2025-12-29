<?php

/**
 * Test: Nette\Application\UI\Presenter redirects and forwards
 */

declare(strict_types=1);

use Nette\Application;
use Nette\Application\Attributes\Persistent;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/functions.php';


class RedirectPresenter extends Application\UI\Presenter
{
	#[Persistent]
	public int $page = 1;


	public function actionDefault(): void
	{
		$this->redirect('other', ['page' => 2]);
	}


	public function actionOther(): void
	{
		$this->terminate();
	}
}


test('redirect() creates RedirectResponse with 302 code', function () {
	$presenter = createPresenter(RedirectPresenter::class);

	$response = $presenter->run(new Application\Request('Redirect', 'GET', ['action' => 'default']));

	Assert::type(Application\Responses\RedirectResponse::class, $response);
	Assert::same(Http\IResponse::S302_Found, $response->getCode());
});


class PermanentRedirectPresenter extends Application\UI\Presenter
{
	public function actionDefault(): void
	{
		$this->redirectPermanent('other');
	}


	public function actionOther(): void
	{
		$this->terminate();
	}
}


test('redirectPermanent() creates RedirectResponse with 301 code', function () {
	$presenter = createPresenter(PermanentRedirectPresenter::class);

	$response = $presenter->run(new Application\Request('PermanentRedirect', 'GET', ['action' => 'default']));

	Assert::type(Application\Responses\RedirectResponse::class, $response);
	Assert::same(Http\IResponse::S301_MovedPermanently, $response->getCode());
});


class ForwardPresenter extends Application\UI\Presenter
{
	public function actionDefault(): void
	{
		$this->forward('other');
	}


	public function actionOther(): void
	{
		$this->terminate();
	}
}


test('forward() creates ForwardResponse', function () {
	$presenter = createPresenter(ForwardPresenter::class);

	$response = $presenter->run(new Application\Request('Forward', 'GET', ['action' => 'default']));

	Assert::type(Application\Responses\ForwardResponse::class, $response);
});


class PersistentParamsPresenter extends Application\UI\Presenter
{
	#[Persistent]
	public int $page = 1;

	#[Persistent]
	public string $lang = 'en';


	public function actionDefault(): void
	{
		$this->redirect('other');
	}


	public function actionOther(): void
	{
		$this->terminate();
	}
}


test('redirect() preserves persistent parameters', function () {
	$presenter = new PersistentParamsPresenter;
	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript('http://localhost')),
		new Http\Response,
		new Application\PresenterFactory,
		new Application\Routers\SimpleRouter,
	);
	$presenter->autoCanonicalize = false;

	$response = $presenter->run(new Application\Request('PersistentParams', 'GET', [
		'action' => 'default',
		'page' => 5,
		'lang' => 'cs',
	]));

	Assert::type(Application\Responses\RedirectResponse::class, $response);
	$url = $response->getUrl();
	Assert::contains('page=5', $url);
	Assert::contains('lang=cs', $url);
});


class OverrideParamsPresenter extends Application\UI\Presenter
{
	#[Persistent]
	public int $page = 1;

	#[Persistent]
	public string $lang = 'en';


	public function actionDefault(): void
	{
		$this->redirect('other', ['page' => 10, 'lang' => 'de']);
	}


	public function actionOther(): void
	{
		$this->terminate();
	}
}


test('redirect() can override persistent parameters', function () {
	$presenter = new OverrideParamsPresenter;
	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript('http://localhost')),
		new Http\Response,
		new Application\PresenterFactory,
		new Application\Routers\SimpleRouter,
	);
	$presenter->autoCanonicalize = false;

	$response = $presenter->run(new Application\Request('OverrideParams', 'GET', [
		'action' => 'default',
		'page' => 5,
		'lang' => 'cs',
	]));

	Assert::type(Application\Responses\RedirectResponse::class, $response);
	$url = $response->getUrl();
	Assert::contains('page=10', $url);
	Assert::contains('lang=de', $url);
});


class UrlRedirectPresenter extends Application\UI\Presenter
{
	public function actionDefault(): void
	{
		$this->redirectUrl('https://example.com/path');
	}
}


test('redirectUrl() creates RedirectResponse to external URL', function () {
	$presenter = new UrlRedirectPresenter;
	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript('http://localhost')),
		new Http\Response,
	);
	$presenter->autoCanonicalize = false;

	$response = $presenter->run(new Application\Request('UrlRedirect', 'GET', ['action' => 'default']));

	Assert::type(Application\Responses\RedirectResponse::class, $response);
	Assert::same('https://example.com/path', $response->getUrl());
});


class PostRedirectPresenter extends Application\UI\Presenter
{
	public function actionDefault(): void
	{
		if ($this->getRequest()->getMethod() === 'POST') {
			$this->redirect('success');
		}
	}


	public function actionSuccess(): void
	{
		$this->terminate();
	}


	public function renderDefault(): void
	{
		$this->terminate();
	}
}


test('POST request redirect uses 302 code', function () {
	$presenter = createPresenter(PostRedirectPresenter::class, post: ['data' => 'value']);

	$response = $presenter->run(new Application\Request('PostRedirect', 'POST', ['action' => 'default']));

	Assert::type(Application\Responses\RedirectResponse::class, $response);
	Assert::same(Http\IResponse::S302_Found, $response->getCode());
});
