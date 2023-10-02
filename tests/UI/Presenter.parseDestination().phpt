<?php

declare(strict_types=1);

use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::same([
	'absolute' => false,
	'path' => 'a:b',
	'signal' => false,
	'args' => null,
	'fragment' => '',
], Presenter::parseDestination('a:b'));

Assert::same([
	'absolute' => false,
	'path' => 'a:b',
	'signal' => true,
	'args' => null,
	'fragment' => '',
], Presenter::parseDestination('a:b!'));

Assert::same([
	'absolute' => true,
	'path' => 'a:b',
	'signal' => false,
	'args' => null,
	'fragment' => '',
], Presenter::parseDestination('//a:b'));

Assert::same([
	'absolute' => false,
	'path' => 'a:b',
	'signal' => false,
	'args' => null,
	'fragment' => '#fragment',
], Presenter::parseDestination('a:b#fragment'));

Assert::same([
	'absolute' => false,
	'path' => 'a:b',
	'signal' => false,
	'args' => [],
	'fragment' => '#fragment',
], Presenter::parseDestination('a:b?#fragment'));

Assert::same([
	'absolute' => false,
	'path' => 'a:b',
	'signal' => false,
	'args' => ['a' => 'b', 'c' => 'd'],
	'fragment' => '#fragment',
], Presenter::parseDestination('a:b?a=b&c=d#fragment'));

Assert::exception(
	fn() => Presenter::parseDestination(''),
	InvalidLinkException::class,
);
