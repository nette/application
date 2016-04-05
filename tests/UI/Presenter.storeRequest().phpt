<?php

/**
 * Test: Nette\Application\UI\Presenter::storeRequest()
 */

use Nette\Http;
use Nette\Application;
use Nette\DI;
use Nette\Security;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class TestPresenter extends Application\UI\Presenter
{
	public function getTemplate() {}
}

class MockSession extends Http\Session
{
	public $testSection;

	public function __construct() {}

	public function getSection($section, $class = Nette\Http\SessionSection::class)
	{
		return $this->testSection;
	}
}

class MockSessionSection implements \ArrayAccess
{
	public $testedKeyExistence;
	public $storedKey;
	public $storedValue;
	public $testExpiration;
	public $testExpirationVariables;

	public function __isset($name)
	{
		$this->testedKeyExistence = $name;
		return FALSE;
	}

	public function __set($name, $value)
	{
		$this->storedKey = $name;
		$this->storedValue = $value;
	}

	public function setExpiration($expiraton, $variables = NULL)
	{
		$this->testExpiration = $expiraton;
		$this->testExpirationVariables = $variables;
	}

	public function offsetExists($name)
	{
		return $this->__isset($name);
	}

	public function offsetSet($name, $value)
	{
		$this->__set($name, $value);
	}

	public function offsetGet($name) {}
	public function offsetUnset($name) {}
}

class MockUser extends Security\User
{
	public function __construct() {}

	public function getId()
	{
		return 'test_id';
	}
}


$presenter = new TestPresenter();
$presenter->injectPrimary(
	NULL,
	NULL,
	new Application\Routers\SimpleRouter,
	new Http\Request(new Http\UrlScript),
	new Http\Response,
	$session = new MockSession,
	$user = new MockUser
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
