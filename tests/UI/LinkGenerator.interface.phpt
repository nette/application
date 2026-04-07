<?php

/**
 * Test: LinkGeneratorInterface contract.
 */

declare(strict_types=1);

use Nette\Application\LinkGenerator;
use Nette\Application\LinkGeneratorInterface;
use Nette\Application\Request;
use Nette\Application\Routers;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('LinkGenerator implements LinkGeneratorInterface', function () {
	$generator = new LinkGenerator(new Routers\SimpleRouter, new Http\UrlScript('http://nette.org/en/'));
	Assert::type(LinkGeneratorInterface::class, $generator);
});


test('getLastRequest() returns null initially', function () {
	$generator = new LinkGenerator(new Routers\SimpleRouter, new Http\UrlScript('http://nette.org/en/'));
	Assert::null($generator->getLastRequest());
});


test('getLastRequest() returns Request after link()', function () {
	$generator = new LinkGenerator(new Routers\SimpleRouter, new Http\UrlScript('http://nette.org/en/'));
	$generator->link('Homepage:default');
	Assert::type(Request::class, $generator->getLastRequest());
});


test('getLastRequest() returns Request even when requestToUrl() fails', function () {
	$generator = new LinkGenerator(new Routers\Route('/', 'Product:'), new Http\UrlScript('http://nette.org/en/'));

	// link() throws because requestToUrl() fails, but createRequest() already set lastRequest
	Assert::exception(
		fn() => $generator->link('Homepage:default', ['id' => 10]),
		Nette\Application\UI\InvalidLinkException::class,
	);

	Assert::type(Request::class, $generator->getLastRequest());
});


test('withReferenceUrl() returns a new instance', function () {
	$generator = new LinkGenerator(new Routers\SimpleRouter, new Http\UrlScript('http://nette.org/en/'));
	$generator2 = $generator->withReferenceUrl('http://nette.org/cs/');
	Assert::type(LinkGeneratorInterface::class, $generator2);
	Assert::notSame($generator, $generator2);
});


test('withReferenceUrl() new instance uses the new reference URL', function () {
	$generator = new LinkGenerator(new Routers\SimpleRouter, new Http\UrlScript('http://nette.org/en/'));
	$generator2 = $generator->withReferenceUrl('http://nette.org/cs/');
	Assert::contains('nette.org/cs/', $generator2->link('Homepage:default'));
});


test('createRequest() returns a Request', function () {
	$generator = new LinkGenerator(new Routers\SimpleRouter, new Http\UrlScript('http://nette.org/en/'));
	$request = $generator->createRequest(null, 'Homepage:default', [], 'link');
	Assert::type(Request::class, $request);
});


test('requestToUrl() converts Request to URL string', function () {
	$generator = new LinkGenerator(new Routers\SimpleRouter, new Http\UrlScript('http://nette.org/en/'));
	$request = $generator->createRequest(null, 'Homepage:default', [], 'link');
	Assert::type('string', $generator->requestToUrl($request));
});
