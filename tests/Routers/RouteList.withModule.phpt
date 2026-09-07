<?php declare(strict_types=1);

use Nette\Application\Routers\RouteList;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$list = new RouteList;
$list
	->withModule('A')
		->addRoute('foo', ['presenter' => 'foo'])
		->withModule('B')
			->addRoute('bar', ['presenter' => 'bar'])
		->end()
	->end()
	->withModule('C')
		->addRoute('hello', ['presenter' => 'hello']);


testRouteIn(
	$list,
	'/foo',
	['presenter' => 'A:foo', 'test' => 'testvalue'],
	'/foo?test=testvalue',
);

testRouteIn(
	$list,
	'/bar',
	['presenter' => 'A:B:bar', 'test' => 'testvalue'],
	'/bar?test=testvalue',
);

testRouteIn(
	$list,
	'/hello',
	['presenter' => 'C:hello', 'test' => 'testvalue'],
	'/hello?test=testvalue',
);

testRouteIn($list, '/none');


// Nette:Micro is exempt from module prefixing in both directions
$list = new RouteList;
$list->withModule('Admin')
	->addRoute('micro', fn() => new Nette\Application\Responses\VoidResponse);

$url = new Nette\Http\UrlScript('http://example.com/micro', '/');
$params = $list->match(new Nette\Http\Request($url));
Assert::same('Nette:Micro', $params['presenter']);
Assert::same('http://example.com/micro', $list->constructUrl($params, $url));
