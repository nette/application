<?php

/**
 * Test: Nette\Application\Routers\RouteList getTargetPresenters().
 */

use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


test(function () {
	$list = new RouteList();
	Assert::same([], $list->getTargetPresenters());
});


test(function () {
	$list = new RouteList();
	$list[] = new Route('about', 'About:default');
	Assert::same(['About'], $list->getTargetPresenters());
});


test(function () {
	$list = new RouteList();
	$list[] = new Route('about', 'About:default');
	$list[] = new Route('<presenter>/<action>');
	Assert::same(NULL, $list->getTargetPresenters());
});


test(function () {
	$list = new RouteList();
	$list[] = new Route('about', 'About:default');
	$list[] = new RouteList();
	$list[1][] = new Route('homepage', 'Homepage:default');
	Assert::same(['About', 'Homepage'], $list->getTargetPresenters());
});


test(function () {
	$list = new RouteList();
	$list[] = new Route('about', 'About:default');
	$list[] = new RouteList();
	$list[1][] = new Route('<presenter>/<action>');
	Assert::same(NULL, $list->getTargetPresenters());
});
