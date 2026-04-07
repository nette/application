<?php

/**
 * Test: LinkGeneratorInterface contract.
 */

declare(strict_types=1);

use Nette\Application\LinkGenerator;
use Nette\Application\LinkGeneratorInterface;
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
	Assert::type(Nette\Application\Request::class, $generator->getLastRequest());
});


test('getLastRequest() is reset at the start of createRequest()', function () {
	$generator = new LinkGenerator(new Routers\SimpleRouter, new Http\UrlScript('http://nette.org/en/'));
	$generator->link('Homepage:default');
	Assert::type(Nette\Application\Request::class, $generator->getLastRequest());

	// createRequest resets lastRequest to null before processing
	try {
		$generator->link('NonExistent:');
	} catch (Nette\Application\UI\InvalidLinkException) {
	}

	// lastRequest was set by createRequest even though requestToUrl failed
	Assert::type(Nette\Application\Request::class, $generator->getLastRequest());
});


test('withReferenceUrl() returns LinkGeneratorInterface instance', function () {
	$generator = new LinkGenerator(new Routers\SimpleRouter, new Http\UrlScript('http://nette.org/en/'));
	$generator2 = $generator->withReferenceUrl('http://nette.org/cs/');
	Assert::type(LinkGeneratorInterface::class, $generator2);
});
