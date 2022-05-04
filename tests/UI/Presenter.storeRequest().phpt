<?php

/**
 * Test: Nette\Application\UI\Presenter::storeRequest()
 */

declare(strict_types=1);

use Nette\Application;
use Nette\Http;
use Nette\Security;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class TestPresenter extends Application\UI\Presenter
{
	public function renderDefault()
	{
		$this->terminate();
	}
}

class MockSession extends Http\Session
{
	public $testSection;


	public function __construct()
	{
	}


	public function getSection(
		string $section,
		string $class = Nette\Http\SessionSection::class,
	): Nette\Http\SessionSection
	{
		return $this->testSection;
	}
}

class MockSessionSection extends Nette\Http\SessionSection
{
	public $testedKeyExistence;

	public $storedKey;

	public $storedValue;

	public $testExpiration;

	public $testExpirationVariables;


	public function __construct()
	{
	}


	public function __isset(string $name): bool
	{
		$this->testedKeyExistence = $name;
		return false;
	}


	public function __set(string $name, $value): void
	{
		$this->storedKey = $name;
		$this->storedValue = $value;
	}


	public function setExpiration($expiraton, $variables = null)
	{
		$this->testExpiration = $expiraton;
		$this->testExpirationVariables = $variables;
	}


	public function offsetExists($name): bool
	{
		return $this->__isset($name);
	}


	public function offsetSet($name, $value): void
	{
		$this->__set($name, $value);
	}


	public function offsetGet($name)
	{
	}


	public function offsetUnset($name): void
	{
	}
}

class MockUser extends Security\User
{
	public function __construct()
	{
	}


	public function getId()
	{
		return 'test_id';
	}
}

test('', function () {
	$presenter = new TestPresenter;
	$presenter->injectPrimary(
		null,
		null,
		new Application\Routers\SimpleRouter,
		new Http\Request(new Http\UrlScript),
		new Http\Response,
		$session = new MockSession,
		$user = new MockUser,
	);

	$section = $session->testSection = new MockSessionSection($session);

	$applicationRequest = new Application\Request('', '', []);
	$presenter->run($applicationRequest);

	$expiration = '+1 year';
	$key = $presenter->storeRequest($expiration);

	Assert::same($expiration, $section->testExpiration);
	Assert::same($key, $section->testExpirationVariables);
	Assert::same($key, $section->testedKeyExistence);
	Assert::same($key, $section->storedKey);
	Assert::same([$user->getId(), $applicationRequest], $section->storedValue);
});

test('', function () {
	$presenter = new TestPresenter;
	$presenter->injectPrimary(
		null,
		null,
		new Application\Routers\SimpleRouter,
		new Http\Request(new Http\UrlScript),
		new Http\Response,
		$session = new MockSession,
	);

	$section = $session->testSection = new MockSessionSection($session);

	$applicationRequest = new Application\Request('', '', []);
	$presenter->run($applicationRequest);

	$expiration = '+1 year';
	$key = $presenter->storeRequest($expiration);

	Assert::same($expiration, $section->testExpiration);
	Assert::same($key, $section->testExpirationVariables);
	Assert::same($key, $section->testedKeyExistence);
	Assert::same($key, $section->storedKey);
	Assert::same([null, $applicationRequest], $section->storedValue);
});
