<?php

/**
 * Test: Nette\Application\Responses\JsonResponse.
 */

use Nette\Application\Responses\JsonResponse;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

if (PHP_SAPI === 'cli') {
	Tester\Environment::skip('Requires CGI SAPI to work with HTTP headers.');
}

test(function () {
	$data = array('text' => 'hello world');
	$encoded = json_encode($data);
	$jsonResponse = new JsonResponse($data, 'application/json');

	ob_start();
	$jsonResponse->send(new Http\Request(new Http\UrlScript), $response = new Http\Response);

	Assert::same($encoded, ob_get_clean());
	Assert::same('application/json; charset=utf-8', $response->getHeader('Content-Type'));
});
