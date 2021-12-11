<?php

/**
 * Test: NetteModule\MicroPresenter
 */

declare(strict_types=1);

use Nette\Application\Request;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


function renderResponse(Nette\Application\Responses\TextResponse $response)
{
	ob_start();
	try {
		$response->send(new Http\Request(new Http\UrlScript), new Http\Response(null));
		return ob_get_clean();
	} catch (Throwable $e) {
		ob_end_clean();
		throw $e;
	}
}


function createContainer()
{
	$latteFactory = Mockery::mock(Nette\Bridges\ApplicationLatte\LatteFactory::class);
	$latteFactory->shouldReceive('create')->andReturn(new Latte\Engine);
	$container = Mockery::mock(Nette\DI\Container::class);
	$container->shouldReceive('getByType')->with('Nette\Bridges\ApplicationLatte\LatteFactory')->andReturn($latteFactory);
	return $container;
}


test('', function () {
	$presenter = new NetteModule\MicroPresenter(createContainer());
	$response = $presenter->run(new Request('Nette:Micro', 'GET', [
		'callback' => function () {
			return 'test';
		},
	]));

	Assert::type(Nette\Application\Responses\TextResponse::class, $response);
	Assert::same('test', renderResponse($response));
});


test('', function () {
	$presenter = new NetteModule\MicroPresenter(createContainer());
	$response = $presenter->run(new Request('Nette:Micro', 'GET', [
		'callback' => function ($param) {
			return $param;
		},
		'param' => 'test',
	]));

	Assert::type(Nette\Application\Responses\TextResponse::class, $response);
	Assert::same('test', renderResponse($response));
});


test('', function () {
	$presenter = new NetteModule\MicroPresenter(createContainer());
	$response = $presenter->run(new Request('Nette:Micro', 'GET', [
		'callback' => function () {
			return '{=date(Y)}';
		},
	]));

	Assert::type(Nette\Application\Responses\TextResponse::class, $response);
	Assert::same(date('Y'), renderResponse($response));
});


test('', function () {
	$presenter = new NetteModule\MicroPresenter(createContainer());
	$response = $presenter->run(new Request('Nette:Micro', 'GET', [
		'callback' => function () {
			return [new SplFileInfo(Tester\FileMock::create('{$param}')), []];
		},
		'param' => 'test',
	]));

	Assert::type(Nette\Application\Responses\TextResponse::class, $response);
	Assert::same('test', renderResponse($response));
});


test('', function () {
	$presenter = new NetteModule\MicroPresenter;

	$response = $presenter->run(new Request('Nette:Micro', 'GET', [
		'callback' => function ($presenter) {
			$template = $presenter->createTemplate(null, function () {
				return new Latte\Engine;
			});
			$template->getLatte()->setLoader(new Latte\Loaders\StringLoader);
			$template->setFile('test');

			return $template;
		},
	]));

	Assert::type(Nette\Application\Responses\TextResponse::class, $response);
	Assert::same('test', renderResponse($response));
});


test('', function () {
	$presenter = new NetteModule\MicroPresenter;

	$response = $presenter->run(new Request('Nette:Micro', 'GET', [
		'callback' => function ($presenter) {
			$template = $presenter->createTemplate(null, function () {
				return new Latte\Engine;
			});
			$template->getLatte()->setLoader(new Latte\Loaders\FileLoader);
			$template->setFile(Tester\FileMock::create('{$param}'));
			$template->setParameters(['param' => 'test']);

			return $template;
		},
	]));

	Assert::type(Nette\Application\Responses\TextResponse::class, $response);
	Assert::same('test', renderResponse($response));
});


test('', function () {
	$filename = 'notfound.latte';
	Assert::exception(function () use ($filename) {
		$presenter = new NetteModule\MicroPresenter;

		$response = $presenter->run(new Request('Nette:Micro', 'GET', [
			'callback' => function ($presenter) use ($filename) {
				$template = $presenter->createTemplate(null, function () {
					return new Latte\Engine;
				});
				$template->getLatte()->setLoader(new Latte\Loaders\FileLoader);
				$template->setFile($filename);
				$template->setParameters(['param' => 'test']);

				return $template;
			},
		]));

		renderResponse($response);
	}, RuntimeException::class, "Missing template file '$filename'.");
});
