<?php

/**
 * Test: NetteModule\MicroPresenter
 */

use Nette\Application\Request;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class LatteFactory implements Nette\Bridges\ApplicationLatte\ILatteFactory
{
	private $engine;

	public function __construct(Latte\Engine $engine)
	{
		$this->engine = $engine;
	}

	public function create()
	{
		return $this->engine;
	}
}


class MicroContainer extends Nette\DI\Container
{

	protected $meta = [
		'types' => [
			Nette\Bridges\ApplicationLatte\ILatteFactory::class => [1 => ['latte.latteFactory']],
		],
	];

	public static function create()
	{
		$container = new self();
		$container->addService('latte.latteFactory', new LatteFactory(new Latte\Engine()));
		return $container;
	}
}


class Responder
{
	public static function render(Nette\Application\Responses\TextResponse $response)
	{
		ob_start();
		$response->send(new Http\Request(new Http\UrlScript()), new Http\Response(NULL));
		return ob_get_clean();
	}
}


test(function () {
	$presenter = new NetteModule\MicroPresenter(MicroContainer::create());
	$response = $presenter->run(new Request('Nette:Micro', 'GET', [
		'callback' => function () {
			return 'test';
		},
	]));

	Assert::type(\Nette\Application\Responses\TextResponse::class, $response);
	Assert::same('test', Responder::render($response));
});


test(function () {
	$presenter = new NetteModule\MicroPresenter(MicroContainer::create());
	$response = $presenter->run(new Request('Nette:Micro', 'GET', [
		'callback' => function ($param) {
			return $param;
		},
		'param' => 'test',
	]));

	Assert::type(Nette\Application\Responses\TextResponse::class, $response);
	Assert::same('test', Responder::render($response));
});


test(function () {
	$presenter = new NetteModule\MicroPresenter(MicroContainer::create());
	$response = $presenter->run(new Request('Nette:Micro', 'GET', [
		'callback' => function () {
			return '{=date(Y)}';
		},
	]));

	Assert::type(Nette\Application\Responses\TextResponse::class, $response);
	Assert::same(date('Y'), Responder::render($response));
});


test(function () {
	$presenter = new NetteModule\MicroPresenter(MicroContainer::create());
	$response = $presenter->run(new Request('Nette:Micro', 'GET', [
		'callback' => function () {
			return [new SplFileInfo(Tester\FileMock::create('{$param}')), []];
		},
		'param' => 'test',
	]));

	Assert::type(Nette\Application\Responses\TextResponse::class, $response);
	Assert::same('test', Responder::render($response));
});


test(function () {
	$latteFactory = new LatteFactory(new Latte\Engine());
	$presenter = new NetteModule\MicroPresenter;

	$response = $presenter->run(new Request('Nette:Micro', 'GET', [
		'callback' => function ($presenter) use ($latteFactory) {
			$template = $presenter->createTemplate(NULL, function () use ($latteFactory) {
				return $latteFactory->create();
			});
			$template->getLatte()->setLoader(new Latte\Loaders\StringLoader);
			$template->setFile('test');

			return $template;
		},
	]));

	Assert::type(Nette\Application\Responses\TextResponse::class, $response);
	Assert::same('test', Responder::render($response));
});


test(function () {
	$latteFactory = new LatteFactory(new Latte\Engine());
	$presenter = new NetteModule\MicroPresenter;

	$response = $presenter->run(new Request('Nette:Micro', 'GET', [
		'callback' => function ($presenter) use ($latteFactory) {
			$template = $presenter->createTemplate(NULL, function () use ($latteFactory) {
				return $latteFactory->create();
			});
			$template->getLatte()->setLoader(new Latte\Loaders\FileLoader());
			$template->setFile(new SplFileInfo(Tester\FileMock::create('{$param}')));
			$template->setParameters(['param' => 'test']);

			return $template;
		},
	]));

	Assert::type(Nette\Application\Responses\TextResponse::class, $response);
	Assert::same('test', Responder::render($response));
});


test(function () {
	$filename = 'notfound.latte';
	Assert::exception(function () use ($filename) {
		$latteFactory = new LatteFactory(new Latte\Engine());
		$presenter = new NetteModule\MicroPresenter;

		$response = $presenter->run(new Request('Nette:Micro', 'GET', [
			'callback' => function ($presenter) use ($latteFactory, $filename) {
				$template = $presenter->createTemplate(NULL, function () use ($latteFactory) {
					return $latteFactory->create();
				});
				$template->getLatte()->setLoader(new Latte\Loaders\FileLoader());
				$template->setFile($filename);
				$template->setParameters(['param' => 'test']);

				return $template;
			},
		]));

		Responder::render($response);
	}, '\RuntimeException', "Missing template file '$filename'.");
});
