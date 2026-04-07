<?php

declare(strict_types=1);

use Nette\Application\DefaultLinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::same([
	'absolute' => false,
	'path' => 'a:b',
	'signal' => false,
	'args' => null,
	'fragment' => '',
], DefaultLinkGenerator::parseDestination('a:b'));

Assert::same([
	'absolute' => false,
	'path' => 'a:b',
	'signal' => true,
	'args' => null,
	'fragment' => '',
], DefaultLinkGenerator::parseDestination('a:b!'));

Assert::same([
	'absolute' => true,
	'path' => 'a:b',
	'signal' => false,
	'args' => null,
	'fragment' => '',
], DefaultLinkGenerator::parseDestination('//a:b'));

Assert::same([
	'absolute' => false,
	'path' => 'a:b',
	'signal' => false,
	'args' => null,
	'fragment' => '#fragment',
], DefaultLinkGenerator::parseDestination('a:b#fragment'));

Assert::same([
	'absolute' => false,
	'path' => 'a:b',
	'signal' => false,
	'args' => [],
	'fragment' => '#fragment',
], @DefaultLinkGenerator::parseDestination('a:b?#fragment')); // deprecated

Assert::same([
	'absolute' => false,
	'path' => 'a:b',
	'signal' => false,
	'args' => ['a' => 'b', 'c' => 'd'],
	'fragment' => '#fragment',
], @DefaultLinkGenerator::parseDestination('a:b?a=b&c=d#fragment')); // deprecated

Assert::exception(
	fn() => DefaultLinkGenerator::parseDestination(''),
	InvalidLinkException::class,
);
