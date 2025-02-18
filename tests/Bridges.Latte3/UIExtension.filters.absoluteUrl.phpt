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

Tester\Environment::bypassFinals();


$latte = new Latte\Engine;
$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension(null));

$request = Mockery::mock(Nette\Http\IRequest::class);
$request->shouldReceive('getUrl')->andReturn(new Nette\Http\UrlScript('https://nette.org/a/b'));
$request->shouldIgnoreMissing();

$response = Mockery::mock(Nette\Http\IResponse::class);
$response->shouldIgnoreMissing();

$presenter = Mockery::mock(Nette\Application\UI\Presenter::class);
$presenter->shouldReceive('getPresenterIfExists')->andReturn($presenter);
$presenter->shouldReceive('getHttpRequest')->andReturn($request);
$presenter->shouldReceive('getHttpResponse')->andReturn($response);
$presenter->shouldIgnoreMissing();


$latte = new Latte\Engine;
$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension($presenter));

Assert::null($latte->invokeFilter('absoluteUrl', [null]));
Assert::same('https://nette.org/a/b', $latte->invokeFilter('absoluteUrl', ['']));
Assert::same('https://nette.org/a/b#foo', $latte->invokeFilter('absoluteUrl', ['#foo']));
Assert::same('https://nette.org/a/foo', $latte->invokeFilter('absoluteUrl', ['foo']));
Assert::same('https://nette.org/foo', $latte->invokeFilter('absoluteUrl', ['/foo']));
Assert::same('https://foo/', $latte->invokeFilter('absoluteUrl', ['//foo']));
Assert::same('https://foo/', $latte->invokeFilter('absoluteUrl', ['https://foo']));


class Foo
{
	public function __toString()
	{
		return 'foo';
	}
}

Assert::same('https://nette.org/a/foo', $latte->invokeFilter('absoluteUrl', [new Foo]));
