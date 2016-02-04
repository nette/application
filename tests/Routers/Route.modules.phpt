<?php

/**
 * Test: Nette\Application\Routers\Route with Modules
 */

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<presenter>', [
	'module' => 'Module:Submodule',
]);

testRouteIn($route, '/abc', 'Module:Submodule:Abc', [
	'test' => 'testvalue',
], '/abc?test=testvalue');

testRouteIn($route, '/');
Assert::null(testRouteOut($route, 'Homepage'));
Assert::null(testRouteOut($route, 'Module:Homepage'));
Assert::same('http://example.com/homepage', testRouteOut($route, 'Module:Submodule:Homepage'));


$route = new Route('<presenter>', [
	'module' => 'Module:Submodule',
	'presenter' => 'Default',
]);

testRouteIn($route, '/', 'Module:Submodule:Default', [
	'test' => 'testvalue',
], '/?test=testvalue');

Assert::null(testRouteOut($route, 'Homepage'));
Assert::null(testRouteOut($route, 'Module:Homepage'));
Assert::same('http://example.com/homepage', testRouteOut($route, 'Module:Submodule:Homepage'));


$route = new Route('<module>/<presenter>', [
	'presenter' => 'AnyDefault',
]);

testRouteIn($route, '/module.submodule', 'Module:Submodule:AnyDefault', [
	'test' => 'testvalue',
], '/module.submodule/?test=testvalue');

Assert::null(testRouteOut($route, 'Homepage'));
Assert::same('http://example.com/module/homepage', testRouteOut($route, 'Module:Homepage'));
Assert::same('http://example.com/module.submodule/homepage', testRouteOut($route, 'Module:Submodule:Homepage'));


$route = new Route('<module>/<presenter>', [
	'module' => 'Module:Submodule',
	'presenter' => 'Default',
]);

testRouteIn($route, '/module.submodule', 'Module:Submodule:Default', [
	'test' => 'testvalue',
], '/?test=testvalue');

Assert::null(testRouteOut($route, 'Homepage'));
Assert::same('http://example.com/module/homepage', testRouteOut($route, 'Module:Homepage'));
Assert::same('http://example.com/module.submodule/homepage', testRouteOut($route, 'Module:Submodule:Homepage'));


$route = new Route('[<module>/]<presenter>');
testRouteIn($route, '/home', 'Home', [
	'test' => 'testvalue',
], '/home?test=testvalue');


$route = new Route('[<module=Def>/]<presenter>');
testRouteIn($route, '/home', 'Def:Home', [
	'test' => 'testvalue',
], '/home?test=testvalue');


$route = new Route('[<module>/]<presenter>');
testRouteIn($route, '/module/home', 'Module:Home', [
	'test' => 'testvalue',
], '/module/home?test=testvalue');


$route = new Route('[<module=def>/]<presenter>');
testRouteIn($route, '/module/home', 'Module:Home', [
	'test' => 'testvalue',
], '/module/home?test=testvalue');


$route = new Route('[<module>/]<presenter>');
testRouteIn($route, '/module.submodule/home', 'Module:Submodule:Home', [
	'test' => 'testvalue',
], '/module.submodule/home?test=testvalue');


$route = new Route('[<module>/]<presenter>');
testRouteIn($route, '/module/submodule.home', 'Module:Submodule:Home', [
	'test' => 'testvalue',
], '/module.submodule/home?test=testvalue');
