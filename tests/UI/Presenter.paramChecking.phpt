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
	new Http\Request(new Http\UrlScript),
	new Http\Response,
	null,
	new Application\Routers\SimpleRouter,
);


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::Get, ['action' => []]);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Action name is not valid.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::Get, ['do' => []]);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Signal name is not string.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::Get, ['a' => []]);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Argument $a passed to ParamPresenter::actionDefault() must be scalar, array given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::Get, ['b' => []]);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Argument $b passed to ParamPresenter::actionDefault() must be scalar, array given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::Get, ['c' => 1]);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Argument $c passed to ParamPresenter::actionDefault() must be array, integer given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::Get, ['d' => 1]);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Argument $d passed to ParamPresenter::actionDefault() must be array, integer given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::Get, ['e' => 1.1]);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Argument $e passed to ParamPresenter::actionDefault() must be integer, double given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::Get, ['e' => '1 ']);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Argument $e passed to ParamPresenter::actionDefault() must be integer, string given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::Get, ['f' => '1 ']);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Argument $f passed to ParamPresenter::actionDefault() must be double, string given.');


Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::Get, ['g' => '']);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, 'Argument $g passed to ParamPresenter::actionDefault() must be boolean, string given.');

Assert::exception(function () use ($presenter) {
	$request = new Application\Request('Test', Http\Request::Get, ['bool' => []]);
	$presenter->run($request);
}, Nette\Application\BadRequestException::class, "Value passed to persistent parameter 'bool' in presenter Test must be boolean, array given.");
