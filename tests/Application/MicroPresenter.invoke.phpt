<?php

/**
 * Test: NetteModule\MicroPresenter
 */

use Nette\Application\Request;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test(function () {
	$presenter = $p = new NetteModule\MicroPresenter;

	$presenter->run(new Request('Nette:Micro', 'GET', array(
		'callback' => function ($id, $page, $presenter) use ($p) {
			Assert::same($p, $presenter);
			Notes::add('Callback id ' . $id . ' page ' . $page);
		},
		'id' => 1,
		'page' => 2,
	)));
	Assert::same(array(
		'Callback id 1 page 2',
	), Notes::fetch());
});
