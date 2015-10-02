<?php

/**
 * Test: Nette\Application\UI\Presenter and checking params.
 */

use Nette\Http;
use Nette\Application;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class TestPresenter extends Application\UI\Presenter
{
	/** @persistent */
	public $bool = TRUE;

	function actionDefault($a, $b = NULL, array $c, array $d = NULL, $e = 1, $f = 1.0, $g = FALSE)
	{}

}


$presenter = new TestPresenter;
$presenter->injectPrimary(
	NULL,
	NULL,
	new Application\Routers\SimpleRouter,
	new Http\Request(new Http\UrlScript),
	new Http\Response
);


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['action' => []]);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Action name is not alphanumeric string.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['do' => []]);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Signal name is not string.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['a' => []]);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Argument $a passed to TestPresenter::actionDefault() must be scalar, array given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['b' => []]);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Argument $b passed to TestPresenter::actionDefault() must be scalar, array given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['c' => 1]);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Argument $c passed to TestPresenter::actionDefault() must be array, integer given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['d' => 1]);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Argument $d passed to TestPresenter::actionDefault() must be array, integer given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['e' => 1.1]);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Argument $e passed to TestPresenter::actionDefault() must be integer, double given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['e' => '1 ']);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Argument $e passed to TestPresenter::actionDefault() must be integer, string given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['f' => '1 ']);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Argument $f passed to TestPresenter::actionDefault() must be double, string given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['g' => '']);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Argument $g passed to TestPresenter::actionDefault() must be boolean, string given.');

Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['bool' => []]);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, "Value passed to persistent parameter 'bool' in presenter Test must be boolean, array given.");
