<?php

/**
 * Test: UIExtension filters
 */

declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}


$latte = new Latte\Engine;
$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension(null));

setlocale(LC_TIME, 'C');
date_default_timezone_set('Europe/Prague');

Assert::null($latte->invokeFilter('modifyDate', [null, null]));
Assert::same('1978-01-24 11:40:00', (string) $latte->invokeFilter('modifyDate', [254_400_000, '+1 day']));
Assert::same('1978-05-06 00:00:00', (string) $latte->invokeFilter('modifyDate', ['1978-05-05', '+1 day']));
Assert::same('1978-05-06 00:00:00', (string) $latte->invokeFilter('modifyDate', [new DateTime('1978-05-05'), '1day']));
Assert::same('1978-01-22 11:40:00', (string) $latte->invokeFilter('modifyDate', [254_400_000, -1, 'day']));
