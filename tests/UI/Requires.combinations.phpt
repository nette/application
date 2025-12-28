<?php

/**
 * Test: #[Requires] attribute combinations
 */

declare(strict_types=1);

use Nette\Application;
use Nette\Application\Attributes\Requires;
use Nette\Http;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/functions.php';


#[Requires(methods: 'POST', ajax: true)]
class PostAjaxPresenter extends Application\UI\Presenter
{
	public function actionDefault(): never
	{
		$this->terminate();
	}
}


test('POST + AJAX requirement both satisfied', function () {
	$presenter = createPresenter(
		PostAjaxPresenter::class,
		method: Http\Request::Post,
		headers: ['X-Requested-With' => 'XMLHttpRequest'],
	);

	Assert::noError(
		fn() => $presenter->run(new Application\Request('Test', Http\Request::Post)),
	);
});


test('POST requirement satisfied but AJAX missing', function () {
	$presenter = createPresenter(
		PostAjaxPresenter::class,
		method: Http\Request::Post,
	);

	Assert::exception(
		fn() => $presenter->run(new Application\Request('Test', Http\Request::Post)),
		Application\BadRequestException::class,
		'AJAX request is required by PostAjaxPresenter',
	);
});


test('AJAX requirement satisfied but wrong HTTP method', function () {
	$presenter = createPresenter(
		PostAjaxPresenter::class,
		headers: ['X-Requested-With' => 'XMLHttpRequest'],
	);

	Assert::exception(
		fn() => $presenter->run(new Application\Request('Test', Http\Request::Get)),
		Application\BadRequestException::class,
		'Method GET is not allowed by PostAjaxPresenter',
	);
});


#[Requires(sameOrigin: true, methods: 'POST')]
class CsrfPostPresenter extends Application\UI\Presenter
{
	public function actionDefault(): never
	{
		$this->terminate();
	}
}


test('sameOrigin + POST both satisfied', function () {
	$presenter = createPresenter(
		CsrfPostPresenter::class,
		method: Http\Request::Post,
		cookies: [Http\Helpers::StrictCookieName => 1],
	);

	Assert::noError(
		fn() => $presenter->run(new Application\Request('Test', Http\Request::Post)),
	);
});


test('sameOrigin satisfied but wrong method', function () {
	$presenter = createPresenter(
		CsrfPostPresenter::class,
		cookies: [Http\Helpers::StrictCookieName => 1],
	);

	Assert::exception(
		fn() => $presenter->run(new Application\Request('Test', Http\Request::Get)),
		Application\BadRequestException::class,
		'Method GET is not allowed by CsrfPostPresenter',
	);
});


test('POST satisfied but sameOrigin violated', function () {
	$presenter = createPresenter(
		CsrfPostPresenter::class,
		method: Http\Request::Post,
	);

	// Without the strict cookie, request is considered cross-origin and redirected
	$response = $presenter->run(new Application\Request('Test', Http\Request::Post));
	Assert::type(Application\Responses\RedirectResponse::class, $response);
});


class MultiRequiresPresenter extends Application\UI\Presenter
{
	#[Requires(methods: 'POST')]
	#[Requires(ajax: true)]
	public function actionDefault(): never
	{
		$this->terminate();
	}
}


test('multiple Requires attributes on same action', function () {
	$presenter = createPresenter(
		MultiRequiresPresenter::class,
		method: Http\Request::Post,
		headers: ['X-Requested-With' => 'XMLHttpRequest'],
	);

	Assert::noError(
		fn() => $presenter->run(new Application\Request('Test', Http\Request::Post, ['action' => 'default'])),
	);
});


test('multiple Requires - first satisfied, second violated', function () {
	$presenter = createPresenter(
		MultiRequiresPresenter::class,
		method: Http\Request::Post,
	);

	Assert::exception(
		fn() => $presenter->run(new Application\Request('Test', Http\Request::Post, ['action' => 'default'])),
		Application\BadRequestException::class,
		'AJAX request is required by MultiRequiresPresenter::actionDefault()',
	);
});


test('multiple Requires - second satisfied, first violated', function () {
	$presenter = createPresenter(
		MultiRequiresPresenter::class,
		headers: ['X-Requested-With' => 'XMLHttpRequest'],
	);

	Assert::exception(
		fn() => $presenter->run(new Application\Request('Test', Http\Request::Get, ['action' => 'default'])),
		Application\BadRequestException::class,
		'Method GET is not allowed by MultiRequiresPresenter::actionDefault()',
	);
});


#[Requires(ajax: true)]
class AjaxPresenterWithAction extends Application\UI\Presenter
{
	#[Requires(methods: 'POST')]
	public function actionEdit(): never
	{
		$this->terminate();
	}


	public function actionDefault(): never
	{
		$this->terminate();
	}
}


test('class-level + method-level requirements combine', function () {
	$presenter = createPresenter(
		AjaxPresenterWithAction::class,
		method: Http\Request::Post,
		headers: ['X-Requested-With' => 'XMLHttpRequest'],
	);

	Assert::noError(
		fn() => $presenter->run(new Application\Request('Test', Http\Request::Post, ['action' => 'edit'])),
	);
});


test('class-level requirement satisfied, method-level violated', function () {
	$presenter = createPresenter(
		AjaxPresenterWithAction::class,
		headers: ['X-Requested-With' => 'XMLHttpRequest'],
	);

	Assert::exception(
		fn() => $presenter->run(new Application\Request('Test', Http\Request::Get, ['action' => 'edit'])),
		Application\BadRequestException::class,
		'Method GET is not allowed by AjaxPresenterWithAction::actionEdit()',
	);
});


test('method-level requirement satisfied, class-level violated', function () {
	$presenter = createPresenter(
		AjaxPresenterWithAction::class,
		method: Http\Request::Post,
	);

	Assert::exception(
		fn() => $presenter->run(new Application\Request('Test', Http\Request::Post, ['action' => 'edit'])),
		Application\BadRequestException::class,
		'AJAX request is required by AjaxPresenterWithAction',
	);
});


#[Requires(forward: true)]
class ForwardPresenter extends Application\UI\Presenter
{
	public function actionDefault(): never
	{
		$this->terminate();
	}
}


test('forward requirement satisfied', function () {
	$presenter = createPresenter(ForwardPresenter::class);

	Assert::noError(
		fn() => $presenter->run(new Application\Request('Test', Application\Request::FORWARD)),
	);
});


test('forward requirement violated', function () {
	$presenter = createPresenter(ForwardPresenter::class);

	Assert::exception(
		fn() => $presenter->run(new Application\Request('Test', Http\Request::Get)),
		Application\BadRequestException::class,
		'Forwarded request is required by ForwardPresenter',
	);
});
