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
	$request = new Application\Request('Test', Http\Request::GET, array('action' => array()));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', 'Action name is not alphanumeric string.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('do' => array()));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', 'Signal name is not string.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('a' => array()));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', 'Argument $a passed to TestPresenter::actionDefault() must be scalar, array given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('b' => array()));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', 'Argument $b passed to TestPresenter::actionDefault() must be scalar, array given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('c' => 1));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', 'Argument $c passed to TestPresenter::actionDefault() must be array, integer given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('d' => 1));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', 'Argument $d passed to TestPresenter::actionDefault() must be array, integer given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('e' => 1.1));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', 'Argument $e passed to TestPresenter::actionDefault() must be integer, double given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('e' => '1 '));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', 'Argument $e passed to TestPresenter::actionDefault() must be integer, string given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('f' => '1 '));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', 'Argument $f passed to TestPresenter::actionDefault() must be double, string given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('g' => ''));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', 'Argument $g passed to TestPresenter::actionDefault() must be boolean, string given.');

Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('bool' => array()));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Value passed to persistent parameter 'bool' in presenter Test must be boolean, array given.");
