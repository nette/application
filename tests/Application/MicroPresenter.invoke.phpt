<?php

/**
 * Test: NetteModule\MicroPresenter
 */

use Nette\Application\Request;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class Invokable
{
	public function __invoke($page, $id, NetteModule\MicroPresenter $presenter)
	{
		$this->log[] = 'Callback id ' . $id . ' page ' . $page;
	}
}


test(function () {
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


test(function () {
	$presenter = new NetteModule\MicroPresenter;

	$presenter->run(new Request('Nette:Micro', 'GET', [
		'callback' => $invokable = new Invokable,
		'id' => 1,
		'page' => 2,
	]));
	Assert::same([
		'Callback id 1 page 2',
	], $invokable->log);
});



test(function () {
	$container = Mockery::mock(Nette\DI\Container::class)
		->shouldReceive('getByType')->with('stdClass', false)->once()->andReturn(new stdClass)
		->mock();

	$presenter = new NetteModule\MicroPresenter($container);

	$presenter->run(new Request('Nette:Micro', 'GET', [
		'callback' => function (stdClass $obj) use (&$log) {
			$log[] = get_class($obj);
		},
	]));
	Assert::same([
		'stdClass',
	], $log);
});
