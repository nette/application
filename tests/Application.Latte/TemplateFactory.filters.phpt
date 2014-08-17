<?php

/**
 * Test: TemplateFactory filters
 */

use Nette\Bridges\ApplicationLatte\TemplateFactory,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class ControlMock extends Nette\Application\UI\Control
{
}

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
$latte = $factory->createTemplate(new ControlMock)->getLatte();


setlocale(LC_TIME, 'C');
date_default_timezone_set('Europe/Prague');

Assert::null( $latte->invokeFilter('modifyDate', array(NULL, NULL)) );
Assert::same( '1978-01-24 11:40:00', (string) $latte->invokeFilter('modifyDate', array(254400000, '+1 day')) );
Assert::same( '1978-05-06 00:00:00', (string) $latte->invokeFilter('modifyDate', array('1978-05-05', '+1 day')) );
Assert::same( '1978-05-06 00:00:00', (string) $latte->invokeFilter('modifyDate', array(new DateTime('1978-05-05'), '1day')) );
Assert::same( '1978-01-22 11:40:00', (string) $latte->invokeFilter('modifyDate', array(254400000, -1, 'day')) );


Assert::same( '%25', $latte->invokeFilter('url', array('%')) );
Assert::same( 3, $latte->invokeFilter('length', array('abc')) );
Assert::same( 2, $latte->invokeFilter('length', array(array(1, 2))) );
Assert::null( $latte->invokeFilter('null', array('x')) );
Assert::same( '', $latte->invokeFilter('normalize', array('  ')) );
Assert::same( 'a-b', $latte->invokeFilter('webalize', array('a b')) );
Assert::same( '  a', $latte->invokeFilter('padLeft', array('a', 3)) );
Assert::same( 'a  ', $latte->invokeFilter('padRight', array('a', 3)) );
Assert::same( 'cba', $latte->invokeFilter('reverse', array('abc')) );
