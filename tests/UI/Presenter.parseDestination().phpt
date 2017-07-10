<?php

declare(strict_types=1);

use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::same([
	'absolute' => FALSE,
	'path' => 'a:b',
	'signal' => FALSE,
	'args' => NULL,
	'fragment' => '',
], Presenter::parseDestination('a:b'));

Assert::same([
	'absolute' => FALSE,
	'path' => 'a:b',
	'signal' => TRUE,
	'args' => NULL,
	'fragment' => '',
], Presenter::parseDestination('a:b!'));

Assert::same([
	'absolute' => TRUE,
	'path' => 'a:b',
	'signal' => FALSE,
	'args' => NULL,
	'fragment' => '',
], Presenter::parseDestination('//a:b'));

Assert::same([
	'absolute' => FALSE,
	'path' => 'a:b',
	'signal' => FALSE,
	'args' => NULL,
	'fragment' => '#fragment',
], Presenter::parseDestination('a:b#fragment'));

Assert::same([
	'absolute' => FALSE,
	'path' => 'a:b',
	'signal' => FALSE,
	'args' => [],
	'fragment' => '#fragment',
], Presenter::parseDestination('a:b?#fragment'));

Assert::same([
	'absolute' => FALSE,
	'path' => 'a:b',
	'signal' => FALSE,
	'args' => ['a' => 'b', 'c' => 'd'],
	'fragment' => '#fragment',
], Presenter::parseDestination('a:b?a=b&c=d#fragment'));

Assert::exception(function () {
	Presenter::parseDestination('');
}, InvalidLinkException::class);
