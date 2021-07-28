<?php

/**
 * Test: Nette\Application\UI\Presenter and checking params.
 */

declare(strict_types=1);

use Nette\Application;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';
@require __DIR__ . '/fixtures/ParamPresenter.php'; // @ Required parameter $c follows optional parameter $b


$presenter = new ParamPresenter;
$presenter->injectPrimary(
	null,
	null,
	new Application\Routers\SimpleRouter,
	new Http\Request(new Http\UrlScript),
	new Http\Response,
);


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['action' => []]);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Action name is not valid.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['do' => []]);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Signal name is not string.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['a' => []]);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Argument $a passed to ParamPresenter::actionDefault() must be scalar, array given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['b' => []]);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Argument $b passed to ParamPresenter::actionDefault() must be scalar, array given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['c' => 1]);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Argument $c passed to ParamPresenter::actionDefault() must be array, int given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['d' => 1]);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Argument $d passed to ParamPresenter::actionDefault() must be array, int given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['e' => 1.1]);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Argument $e passed to ParamPresenter::actionDefault() must be int, float given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['e' => '1 ']);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Argument $e passed to ParamPresenter::actionDefault() must be int, string given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['f' => '1 ']);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Argument $f passed to ParamPresenter::actionDefault() must be float, string given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['g' => '']);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Argument $g passed to ParamPresenter::actionDefault() must be bool, string given.');

Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, ['bool' => []]);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, "Value passed to persistent parameter 'bool' in presenter Test must be bool, array given.");
