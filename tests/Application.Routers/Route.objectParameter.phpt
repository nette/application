<?php

/**
 * Test: Nette\Application\Routers\Route with object parameter
 */

use Nette\Application\Routers\IObjectParameter,
	Nette\Application\Routers\Route,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


class ObjectParameter implements IObjectParameter
{
	public function __toString()
	{
		return 'value';
	}
}

$route = new Route('', array(
	'presenter' => 'Presenter',
));

$params = array(
	'entity' => new ObjectParameter(),
	'array' => array(
		new ObjectParameter(),
	),
);

Assert::same('http://example.com/?entity=value&array%5B0%5D=value', testRouteOut($route, 'Presenter', $params));
