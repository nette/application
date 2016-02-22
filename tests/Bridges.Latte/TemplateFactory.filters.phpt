<?php

/**
 * Test: TemplateFactory filters
 */

use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class LatteFactory implements Nette\Bridges\ApplicationLatte\ILatteFactory
{
	private $engine;

	public function __construct(Latte\Engine $engine)
	{
		$this->engine = $engine;
	}

	public function create()
	{
		return $this->engine;
	}
}

$factory = new TemplateFactory(new LatteFactory(new Latte\Engine));
$latte = $factory->createTemplate()->getLatte();


setlocale(LC_TIME, 'C');
date_default_timezone_set('Europe/Prague');

Assert::null($latte->invokeFilter('modifyDate', [NULL, NULL]));
Assert::same('1978-01-24 11:40:00', (string) $latte->invokeFilter('modifyDate', [254400000, '+1 day']));
Assert::same('1978-05-06 00:00:00', (string) $latte->invokeFilter('modifyDate', ['1978-05-05', '+1 day']));
Assert::same('1978-05-06 00:00:00', (string) $latte->invokeFilter('modifyDate', [new DateTime('1978-05-05'), '1day']));
Assert::same('1978-01-22 11:40:00', (string) $latte->invokeFilter('modifyDate', [254400000, -1, 'day']));


Assert::same('%25', $latte->invokeFilter('url', ['%']));
Assert::null($latte->invokeFilter('null', ['x']));
Assert::same('', $latte->invokeFilter('normalize', ['  ']));
Assert::same('a-b', $latte->invokeFilter('webalize', ['a b']));
Assert::same('  a', $latte->invokeFilter('padLeft', ['a', 3]));
Assert::same('a  ', $latte->invokeFilter('padRight', ['a', 3]));
Assert::same('cba', $latte->invokeFilter('reverse', ['abc']));
