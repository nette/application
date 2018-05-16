<?php

/**
 * Test: TemplateFactory in Bridge properly handles TemplateFactory::onCreate
 */

use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test(function () {
	$engine = new Latte\Engine;
	$latteFactory = Mockery::mock(ILatteFactory::class);
	$latteFactory->shouldReceive('create')->andReturn($engine);
	$factory = new TemplateFactory($latteFactory, new Http\Request(new Http\UrlScript('http://nette.org')));
	$factory->onCreate[] = $callback = function (Template $template) {
		$template->add('foo', 'bar');
	};

	$template = $factory->createTemplate();

	Assert::type('array', $factory->onCreate);
	Assert::same($callback, $factory->onCreate[0]); // our callback
	Assert::equal('bar', $template->foo);
});
