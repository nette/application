<?php

/**
 * Test: NetteModule\MicroPresenter
 */

use Nette\Application\Request,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class Invokable extends Nette\Object
{
	public function __invoke($page, $id, NetteModule\MicroPresenter $presenter)
	{
		Notes::add('Callback id ' . $id . ' page ' . $page);
	}
}


test(function() {
	$presenter = $p = new NetteModule\MicroPresenter;

	$presenter->run(new Request('Nette:Micro', 'GET', [
		'callback' => function($id, $page, $presenter) use ($p) {
			Assert::same($p, $presenter);
			Notes::add('Callback id ' . $id . ' page ' . $page);
		},
		'id' => 1,
		'page' => 2,
	]));
	Assert::same([
		'Callback id 1 page 2'
	], Notes::fetch());
});


test(function() {
	$presenter = new NetteModule\MicroPresenter;

	$presenter->run(new Request('Nette:Micro', 'GET', [
		'callback' => new Invokable(),
		'id' => 1,
		'page' => 2,
	]));
	Assert::same([
		'Callback id 1 page 2'
	], Notes::fetch());
});
