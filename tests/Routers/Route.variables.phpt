<?php

/**
 * Test: Nette\Application\Routers\Route with %variables%
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


testRouteIn(new Route('//<?%domain%>/<path>', 'Default:default'), '/abc', [
	'presenter' => 'Default',
	'path' => 'abc',
	'action' => 'default',
	'test' => 'testvalue',
], '/abc?test=testvalue');


testRouteIn(new Route('//example.<?%tld%>/<path>', 'Default:default'), '/abc', [
	'presenter' => 'Default',
	'path' => 'abc',
	'action' => 'default',
	'test' => 'testvalue',
], '/abc?test=testvalue');


testRouteIn(new Route('//example.com/<?%basePath%>/<path>', 'Default:default'), '/abc', [
	'presenter' => 'Default',
	'path' => 'abc',
	'action' => 'default',
	'test' => 'testvalue',
], '/abc?test=testvalue');


testRouteIn(new Route('//%domain%/<path>', 'Default:default'), '/abc', [
	'presenter' => 'Default',
	'path' => 'abc',
	'action' => 'default',
	'test' => 'testvalue',
], '/abc?test=testvalue');


testRouteIn(new Route('//%sld%.com/<path>', 'Default:default'), '/abc', [
	'presenter' => 'Default',
	'path' => 'abc',
	'action' => 'default',
	'test' => 'testvalue',
], '/abc?test=testvalue');


testRouteIn(new Route('//%sld%.%tld%/<path>', 'Default:default'), '/abc', [
	'presenter' => 'Default',
	'path' => 'abc',
	'action' => 'default',
	'test' => 'testvalue',
], '/abc?test=testvalue');


testRouteIn(new Route('//%host%/<path>', 'Default:default'), '/abc', [
	'presenter' => 'Default',
	'path' => 'abc',
	'action' => 'default',
	'test' => 'testvalue',
], '/abc?test=testvalue');


// alternative
testRouteIn(new Route('//example.%tld%/<path>', 'Default:default'), '/abc', [
	'presenter' => 'Default',
	'path' => 'abc',
	'action' => 'default',
	'test' => 'testvalue',
], '/abc?test=testvalue');


testRouteIn(new Route('//example.com/%basePath%/<path>', 'Default:default'), '/abc', [
	'presenter' => 'Default',
	'path' => 'abc',
	'action' => 'default',
	'test' => 'testvalue',
], '/abc?test=testvalue');


// IP
$url = new Nette\Http\UrlScript('http://192.168.100.100/');
$httpRequest = new Nette\Http\Request($url);
$route = new Route('//%domain%/', 'Default:default');
Assert::same('http://192.168.100.100/', $route->constructUrl($route->match($httpRequest), $url));

$route = new Route('//%tld%/', 'Default:default');
Assert::same('http://192.168.100.100/', $route->constructUrl($route->match($httpRequest), $url));


$url = new Nette\Http\UrlScript('http://[2001:db8::1428:57ab]/');
$httpRequest = new Nette\Http\Request($url);
$route = new Route('//%domain%/', 'Default:default');
Assert::same('http://[2001:db8::1428:57ab]/', $route->constructUrl($route->match($httpRequest), $url));

$route = new Route('//%tld%/', 'Default:default');
Assert::same('http://[2001:db8::1428:57ab]/', $route->constructUrl($route->match($httpRequest), $url));


// special
$url = new Nette\Http\UrlScript('http://localhost/');
$httpRequest = new Nette\Http\Request($url);
$route = new Route('//%domain%/', 'Default:default');
Assert::same('http://localhost/', $route->constructUrl($route->match($httpRequest), $url));

$route = new Route('//%tld%/', 'Default:default');
Assert::same('http://localhost/', $route->constructUrl($route->match($httpRequest), $url));


// host
$url = new Nette\Http\UrlScript('http://www.example.com/');
$httpRequest = new Nette\Http\Request($url);
$route = new Route('//%host%/', 'Default:default');
Assert::same('http://www.example.com/', $route->constructUrl($route->match($httpRequest), $url));
