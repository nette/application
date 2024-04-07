<?php

/**
 * Test: Nette\Application\UI\Component::redirect()
 */

declare(strict_types=1);

use Nette\Application;
use Nette\Application\PresenterFactory;
use Nette\Http;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class TestPresenter extends Application\UI\Presenter
{
}


$factory = new PresenterFactory;
$factory->setAliases([
	'a' => 'Test:a',
	'b' => 'Test:b',
]);

Assert::same('Test:a', $factory->getAlias('a'));
Assert::same('Test:b', $factory->getAlias('b'));
Assert::exception(
	fn() => $factory->getAlias('c'),
	Nette\InvalidStateException::class,
	"Link alias 'c' was not found.",
);



// link generator
$generator = new Application\LinkGenerator(
	new Application\Routers\SimpleRouter,
	new Http\UrlScript('http://localhost'),
	$factory,
);

Assert::same('http://localhost/?action=a&presenter=Test', $generator->link('@a'));


// presenter
$presenter = new TestPresenter;
$presenter->injectPrimary(
	new Http\Request(new Http\UrlScript('http://localhost')),
	new Http\Response,
	$factory,
	new Application\Routers\SimpleRouter,
);


Assert::same('/?action=a&presenter=Test', $presenter->link('@a'));
