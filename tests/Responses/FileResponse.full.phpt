<?php

/**
 * Test: Nette\Application\Responses\FileResponse.
 */

declare(strict_types=1);

use Nette\Application\Responses\FileResponse;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


/* A small file */
test(function () {
	$file = __FILE__;
	$fileResponse = new FileResponse($file);
	$origData = file_get_contents($file);

	ob_start();
	$fileResponse->send(new Http\Request(new Http\UrlScript), new Http\Response);
	Assert::same($origData, ob_get_clean());
});

/* A big file */
test(function () {
	$file = Tester\FileMock::create();

	$data = '';
	for ($i = 0; $i < 20000; $i++) {
		$data .= 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi efficitur nisl mauris, ' .
			'sed bibendum leo tempor ornare. Etiam sodales enim sem. Proin lobortis metus at sagittis ' .
			'imperdiet. In non fringilla libero, ac porta purus. Nam gravida quis tellus quis semper.';
	}

	file_put_contents($file, $data);
	$fileResponse = new FileResponse($file);

	ob_start();
	$fileResponse->send(new Http\Request(new Http\UrlScript), new Http\Response);
	Assert::same($data, ob_get_clean());
});
