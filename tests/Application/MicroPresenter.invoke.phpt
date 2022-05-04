<?php

/**
 * Test: NetteModule\MicroPresenter
 */

declare(strict_types=1);

use Nette\Application\Request;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('', function () {
	$presenter = $p = new NetteModule\MicroPresenter;

	$presenter->run(new Request('Nette:Micro', 'GET', [
		'callback' => function ($id, $page, $presenter) use ($p, &$log) {
			Assert::same($p, $presenter);
			$log[] = 'Callback id ' . $id . ' page ' . $page;
		},
		'id' => 1,
		'page' => 2,
	]));
	Assert::same([
		'Callback id 1 page 2',
	], $log);
});


test('', function () {
	$container = Mockery::mock(Nette\DI\Container::class)
		->shouldReceive('getByType')->with('stdClass', false)->once()->andReturn(new stdClass)
		->mock();

	$presenter = new NetteModule\MicroPresenter($container);

	$presenter->run(new Request('Nette:Micro', 'GET', [
		'callback' => function (stdClass $obj) use (&$log) {
			$log[] = $obj::class;
		},
	]));
	Assert::same([
		'stdClass',
	], $log);
});
