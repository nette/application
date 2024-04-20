<?php

declare(strict_types=1);

use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::same([
	'absolute' => false,
	'path' => 'a:b',
	'signal' => false,
	'args' => null,
	'fragment' => '',
], LinkGenerator::parseDestination('a:b'));

Assert::same([
	'absolute' => false,
	'path' => 'a:b',
	'signal' => true,
	'args' => null,
	'fragment' => '',
], LinkGenerator::parseDestination('a:b!'));

Assert::same([
	'absolute' => true,
	'path' => 'a:b',
	'signal' => false,
	'args' => null,
	'fragment' => '',
], LinkGenerator::parseDestination('//a:b'));

Assert::same([
	'absolute' => false,
	'path' => 'a:b',
	'signal' => false,
	'args' => null,
	'fragment' => '#fragment',
], LinkGenerator::parseDestination('a:b#fragment'));

Assert::same([
	'absolute' => false,
	'path' => 'a:b',
	'signal' => false,
	'args' => [],
	'fragment' => '#fragment',
], LinkGenerator::parseDestination('a:b?#fragment'));

Assert::same([
	'absolute' => false,
	'path' => 'a:b',
	'signal' => false,
	'args' => ['a' => 'b', 'c' => 'd'],
	'fragment' => '#fragment',
], LinkGenerator::parseDestination('a:b?a=b&c=d#fragment'));

Assert::exception(
	fn() => LinkGenerator::parseDestination(''),
	InvalidLinkException::class,
);
