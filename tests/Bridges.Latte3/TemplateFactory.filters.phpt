<?php

/**
 * Test: TemplateFactory filters
 * @phpVersion 8.0
 */

declare(strict_types=1);

use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}


class LatteFactory implements Nette\Bridges\ApplicationLatte\LatteFactory
{
	private $engine;


	public function __construct(Latte\Engine $engine)
	{
		$this->engine = $engine;
	}


	public function create(): Latte\Engine
	{
		return $this->engine;
	}
}

$factory = new TemplateFactory(new LatteFactory(new Latte\Engine));
$latte = $factory->createTemplate()->getLatte();


setlocale(LC_TIME, 'C');
date_default_timezone_set('Europe/Prague');

Assert::null($latte->invokeFilter('modifyDate', [null, null]));
Assert::same('1978-01-24 11:40:00', (string) $latte->invokeFilter('modifyDate', [254400000, '+1 day']));
Assert::same('1978-05-06 00:00:00', (string) $latte->invokeFilter('modifyDate', ['1978-05-05', '+1 day']));
Assert::same('1978-05-06 00:00:00', (string) $latte->invokeFilter('modifyDate', [new DateTime('1978-05-05'), '1day']));
Assert::same('1978-01-22 11:40:00', (string) $latte->invokeFilter('modifyDate', [254400000, -1, 'day']));
