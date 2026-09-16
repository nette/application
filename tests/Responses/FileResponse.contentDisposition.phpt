<?php declare(strict_types=1);

/**
 * Test: Nette\Application\Responses\FileResponse.
 */

use Nette\Application\Responses\FileResponse;
use Nette\Http;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (PHP_SAPI === 'cli') {
	Tester\Environment::skip('Requires CGI SAPI to work with HTTP headers.');
}


test('default attachment content disposition', function () {
	$file = __FILE__;
	$fileResponse = new FileResponse($file);
	$origData = file_get_contents($file);

	$fileInfo = pathinfo($file);
	$fileName = $fileInfo['filename'] . '.' . $fileInfo['extension'];

	ob_start();
	$fileResponse->send(new Http\Request(new Http\UrlScript), $response = new Http\Response);

	Assert::same($origData, ob_get_clean());
	Assert::same(
		'attachment; filename="' . $fileName . '"; filename*=utf-8\'\'' . rawurlencode($fileName),
		$response->getHeader('Content-Disposition'),
	);
});


test('inline content disposition', function () {
	$file = __FILE__;
	$fileResponse = new FileResponse($file, forceDownload: false);
	$origData = file_get_contents($file);

	$fileInfo = pathinfo($file);
	$fileName = $fileInfo['filename'] . '.' . $fileInfo['extension'];

	ob_start();
	$fileResponse->send(new Http\Request(new Http\UrlScript), $response = new Http\Response);

	Assert::same($origData, ob_get_clean());
	Assert::same(
		'inline; filename="' . $fileName . '"; filename*=utf-8\'\'' . rawurlencode($fileName),
		$response->getHeader('Content-Disposition'),
	);
});


test('filename with special characters', function () {
	$file = __FILE__;
	$fileName = 'žluťoučký kůň.txt';
	$fileResponse = new FileResponse($file, $fileName);
	$origData = file_get_contents($file);

	ob_start();
	$fileResponse->send(new Http\Request(new Http\UrlScript), $response = new Http\Response);

	Assert::same($origData, ob_get_clean());
	Assert::same(
		'attachment; filename="' . $fileName . '"; filename*=utf-8\'\'' . rawurlencode($fileName),
		$response->getHeader('Content-Disposition'),
	);
});


test('filename injection into Content-Disposition is neutralized', function () {
	$file = __FILE__;
	$fileName = 'evil".pdf; filename="hack.exe';
	$fileResponse = new FileResponse($file, $fileName);

	ob_start();
	$fileResponse->send(new Http\Request(new Http\UrlScript), $response = new Http\Response);
	ob_get_clean();

	// quotes and backslashes are stripped from the quoted filename, so the header cannot be broken apart
	$header = $response->getHeader('Content-Disposition');
	Assert::same('attachment; filename="evil.pdf; filename=hack.exe"; filename*=utf-8\'\'' . rawurlencode($fileName), $header);
});
