<?php

/**
 * Test: TemplateFactory in Bridge properly handles Latte::onCompile
 */

use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


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



test(function () {
	$engine = new Latte\Engine;
	$factory = new TemplateFactory(new LatteFactoryMock($engine), new Http\Request(new Http\UrlScript('http://nette.org')));
	$engine->onCompile[] = $callback = function () {};

	$factory->createTemplate();

	Assert::type('array', $engine->onCompile);
	Assert::type('Closure', $engine->onCompile[0]); // prepended by TemplateFactory
	Assert::same($callback, $engine->onCompile[1]); // our callback
});


test(function () {
	$engine = new Latte\Engine;
	$factory = new TemplateFactory(new LatteFactoryMock($engine), new Http\Request(new Http\UrlScript('http://nette.org')));
	$engine->onCompile = new ArrayIterator([$callback = function () {}]);

	$factory->createTemplate();

	Assert::type('array', $engine->onCompile);
	Assert::type('Closure', $engine->onCompile[0]); // prepended by TemplateFactory
	Assert::same($callback, $engine->onCompile[1]); // our callback
});


test(function () {
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
	$engine->onCompile = new Event([$callback = function () {}]);

	$factory->createTemplate();

	Assert::type('array', $engine->onCompile);
	Assert::type('Closure', $engine->onCompile[0]); // prepended by TemplateFactory
	Assert::same($callback, $engine->onCompile[1]); // our callback
});
