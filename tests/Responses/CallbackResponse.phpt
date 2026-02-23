<?php declare(strict_types=1);

/**
 * Test: Nette\Application\Responses\CallbackResponse.
 */

use Nette\Application\Responses\CallbackResponse;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('callback execution on send', function () {
	$response = new CallbackResponse(function (Http\IRequest $request, Http\IResponse $response) use (&$ok) {
		$ok = true;
	});
	$response->send(new Http\Request(new Http\UrlScript), new Http\Response);
	Assert::true($ok);
});
