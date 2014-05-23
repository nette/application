<?php

/**
 * Test: TemplateFactory in Bridge properly handles Latte::onCompile
 */

use Nette\Bridges\ApplicationLatte\TemplateFactory,
	Nette\Http,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class ControlMock extends Nette\Application\UI\Control
{
}

class LatteFactoryMock implements Nette\Bridges\ApplicationLatte\ILatteFactory
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



test(function() {
	$engine = new Latte\Engine;
	$factory = new TemplateFactory(new LatteFactoryMock($engine), new Http\Request(new Http\UrlScript('http://nette.org')));
	$engine->onCompile[] = $callback = function() { };

	$factory->createTemplate(new ControlMock);

	Assert::type('array', $engine->onCompile);
	Assert::type('Closure', $engine->onCompile[0]); // prepended by TemplateFactory
	Assert::same($callback, $engine->onCompile[1]); // our callback
});


test(function() {
	$engine = new Latte\Engine;
	$factory = new TemplateFactory(new LatteFactoryMock($engine), new Http\Request(new Http\UrlScript('http://nette.org')));
	$engine->onCompile = new ArrayIterator(array($callback = function() {}));

	$factory->createTemplate(new ControlMock);

	Assert::type('array', $engine->onCompile);
	Assert::type('Closure', $engine->onCompile[0]); // prepended by TemplateFactory
	Assert::same($callback, $engine->onCompile[1]); // our callback
});


test(function() {
	class Event implements IteratorAggregate
	{
		public $events;

		public function __construct($events)
		{
			$this->events = $events;
		}

		public function getIterator()
		{
			return new ArrayIterator($this->events);
		}
	}

	$engine = new Latte\Engine;
	$factory = new TemplateFactory(new LatteFactoryMock($engine), new Http\Request(new Http\UrlScript('http://nette.org')));
	$engine->onCompile = new Event(array($callback = function() {}));

	$factory->createTemplate(new ControlMock);

	Assert::type('array', $engine->onCompile);
	Assert::type('Closure', $engine->onCompile[0]); // prepended by TemplateFactory
	Assert::same($callback, $engine->onCompile[1]); // our callback
});
