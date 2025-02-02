<?php

/**
 * Test: Nette\Application\Responses\FileResponse and range.
 * @httpCode   -
 */

declare(strict_types=1);

use Nette\Application\Responses\FileResponse;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$file = __FILE__;
$fileResponse = new FileResponse($file);
$origData = file_get_contents($file);

test('partial content with byte range', function () use ($fileResponse, $origData) {
	ob_start();
	$fileResponse->send(
		new Http\Request(new Http\UrlScript, headers: ['range' => 'bytes=10-20']),
		$response = new Http\Response,
	);
	Assert::same(substr($origData, 10, 11), ob_get_clean());
	Assert::same(206, $response->getCode());
});


test('single byte range request', function () use ($fileResponse, $origData) {
	ob_start();
	$fileResponse->send(
		new Http\Request(new Http\UrlScript, headers: ['range' => 'bytes=10-10']),
		new Http\Response,
	);
	Assert::same(substr($origData, 10, 1), ob_get_clean());
});


test('range from offset to end', function () use ($fileResponse, $origData, $file) {
	ob_start();
	$fileResponse->send(
		new Http\Request(new Http\UrlScript, headers: ['range' => 'bytes=10-' . filesize($file)]),
		new Http\Response,
	);
	Assert::same(substr($origData, 10), ob_get_clean());
});


test('range starting at offset', function () use ($fileResponse, $origData) {
	ob_start();
	$fileResponse->send(
		new Http\Request(new Http\UrlScript, headers: ['range' => 'bytes=20-']),
		new Http\Response,
	);
	Assert::same(substr($origData, 20), ob_get_clean());
});


test('last byte range request', function () use ($fileResponse, $origData, $file) {
	ob_start();
	$fileResponse->send(
		new Http\Request(new Http\UrlScript, headers: ['range' => 'bytes=' . (filesize($file) - 1) . '-']),
		new Http\Response,
	);
	Assert::same(substr($origData, -1), ob_get_clean());
});


test('invalid byte range handling', function () use ($fileResponse, $file) {
	ob_start();
	$fileResponse->send(
		new Http\Request(new Http\UrlScript, headers: ['range' => 'bytes=' . filesize($file) . '-']),
		$response = new Http\Response,
	);
	Assert::same('', ob_get_clean());
	Assert::same(416, $response->getCode());
});


test('negative offset range (last bytes)', function () use ($fileResponse, $origData) {
	ob_start();
	$fileResponse->send(
		new Http\Request(new Http\UrlScript, headers: ['range' => 'bytes=-20']),
		new Http\Response,
	);
	Assert::same(substr($origData, -20), ob_get_clean());
});


test('full content via negative range', function () use ($fileResponse, $origData, $file) {
	ob_start();
	$fileResponse->send(
		new Http\Request(new Http\UrlScript, headers: ['range' => 'bytes=-' . filesize($file)]),
		new Http\Response,
	);
	Assert::same($origData, ob_get_clean());
});
