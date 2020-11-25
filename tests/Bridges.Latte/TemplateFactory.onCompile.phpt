<?php

/**
 * Test: TemplateFactory in Bridge properly handles Latte::onCompile
 */

declare(strict_types=1);

use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('', function () {
	$engine = new Latte\Engine;
	$latteFactory = Mockery::mock(LatteFactory::class);
	$latteFactory->shouldReceive('create')->andReturn($engine);
	$factory = new TemplateFactory($latteFactory, new Http\Request(new Http\UrlScript('http://nette.org')));
	$engine->onCompile[] = $callback = function () {};

	$factory->createTemplate();

	Assert::type('array', $engine->onCompile);
	Assert::type(Closure::class, $engine->onCompile[0]); // prepended by TemplateFactory
	Assert::same($callback, $engine->onCompile[1]); // our callback
});


test('', function () {
	$engine = new Latte\Engine;
	$latteFactory = Mockery::mock(LatteFactory::class);
	$latteFactory->shouldReceive('create')->andReturn($engine);
	$factory = new TemplateFactory($latteFactory, new Http\Request(new Http\UrlScript('http://nette.org')));
	$engine->onCompile = new ArrayIterator([$callback = function () {}]);

	$factory->createTemplate();

	Assert::type('array', $engine->onCompile);
	Assert::type(Closure::class, $engine->onCompile[0]); // prepended by TemplateFactory
	Assert::same($callback, $engine->onCompile[1]); // our callback
});


test('', function () {
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
	$latteFactory = Mockery::mock(LatteFactory::class);
	$latteFactory->shouldReceive('create')->andReturn($engine);
	$factory = new TemplateFactory($latteFactory, new Http\Request(new Http\UrlScript('http://nette.org')));
	$engine->onCompile = new Event([$callback = function () {}]);

	$factory->createTemplate();

	Assert::type('array', $engine->onCompile);
	Assert::type(Closure::class, $engine->onCompile[0]); // prepended by TemplateFactory
	Assert::same($callback, $engine->onCompile[1]); // our callback
});
