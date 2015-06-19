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
}, 'Nette\Application\BadRequestException', 'Action name is not alphanumeric string.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['do' => []]);
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', 'Signal name is not string.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['a' => []]);
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'a' in method TestPresenter::actionDefault(), expected scalar.");


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['b' => []]);
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'b' in method TestPresenter::actionDefault(), expected scalar.");


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['c' => 1]);
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'c' in method TestPresenter::actionDefault(), expected array.");


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['d' => 1]);
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'd' in method TestPresenter::actionDefault(), expected array.");


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['e' => 1.1]);
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'e' in method TestPresenter::actionDefault(), expected integer.");


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['e' => '1 ']);
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'e' in method TestPresenter::actionDefault(), expected integer.");


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['f' => '1 ']);
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'f' in method TestPresenter::actionDefault(), expected double.");


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['g' => '']);
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'g' in method TestPresenter::actionDefault(), expected boolean.");

Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['bool' => []]);
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for persistent parameter 'bool' in 'Test', expected boolean.");
