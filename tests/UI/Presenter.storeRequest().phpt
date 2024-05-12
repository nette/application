<?php

/**
 * Test: Nette\Application\UI\Presenter::storeRequest()
 */

declare(strict_types=1);

use Nette\Application;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class TestPresenter extends Application\UI\Presenter
{
	public function renderDefault()
	{
		$this->terminate();
	}
}


test('ok', function () {
	$testedKeyExistence = $storedKey = $storedValue = $testExpiration = $testExpirationVariables = null;

	$sessionSectionMock = Mockery::mock(Nette\Http\SessionSection::class);
	$sessionSectionMock->shouldReceive('setExpiration')
		->andReturnUsing(function ($expiration, $variables = null) use (&$testExpiration, &$testExpirationVariables, $sessionSectionMock) {
			$testExpiration = $expiration;
			$testExpirationVariables = $variables;
			return $sessionSectionMock;
		});

	$sessionSectionMock->shouldReceive('offsetExists')
		->andReturnUsing(function ($name) use (&$testedKeyExistence) {
			$testedKeyExistence = $name;
			return false;
		});

	$sessionSectionMock->shouldReceive('offsetSet')
		->andReturnUsing(function ($name, $value) use (&$storedKey, &$storedValue) {
			$storedKey = $name;
			$storedValue = $value;
		});

	$sessionMock = Mockery::mock(Nette\Http\Session::class);
	$sessionMock->shouldReceive('getSection')
		->andReturn($sessionSectionMock);

	$userMock = Mockery::mock(Nette\Security\User::class);
	$userMock->shouldReceive('getId')
		->andReturn('test_id');


	$presenter = new TestPresenter;
	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript),
		new Http\Response,
		null,
		new Application\Routers\SimpleRouter,
		$sessionMock,
		$userMock,
	);

	$applicationRequest = new Application\Request('', '', []);
	$presenter->run($applicationRequest);

	$expiration = '+1 year';
	$key = $presenter->storeRequest($expiration);

	Assert::same($expiration, $testExpiration);
	Assert::same($key, $testExpirationVariables);
	Assert::same($key, $testedKeyExistence);
	Assert::same($key, $storedKey);
	Assert::same([$userMock->getId(), $applicationRequest], $storedValue);
});


test('no user', function () {
	$testedKeyExistence = $storedKey = $storedValue = $testExpiration = $testExpirationVariables = null;

	$sessionSectionMock = Mockery::mock(Nette\Http\SessionSection::class);
	$sessionSectionMock->shouldReceive('setExpiration')
		->andReturnUsing(function ($expiration, $variables = null) use (&$testExpiration, &$testExpirationVariables, $sessionSectionMock) {
			$testExpiration = $expiration;
			$testExpirationVariables = $variables;
			return $sessionSectionMock;
		});

	$sessionSectionMock->shouldReceive('offsetExists')
		->andReturnUsing(function ($name) use (&$testedKeyExistence) {
			$testedKeyExistence = $name;
			return false;
		});

	$sessionSectionMock->shouldReceive('offsetSet')
		->andReturnUsing(function ($name, $value) use (&$storedKey, &$storedValue) {
			$storedKey = $name;
			$storedValue = $value;
		});

	$sessionMock = Mockery::mock(Nette\Http\Session::class);
	$sessionMock->shouldReceive('getSection')
		->andReturn($sessionSectionMock);


	$presenter = new TestPresenter;
	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript),
		new Http\Response,
		null,
		new Application\Routers\SimpleRouter,
		$sessionMock,
	);

	$applicationRequest = new Application\Request('', '', []);
	$presenter->run($applicationRequest);

	$expiration = '+1 year';
	$key = $presenter->storeRequest($expiration);

	Assert::same($expiration, $testExpiration);
	Assert::same($key, $testExpirationVariables);
	Assert::same($key, $testedKeyExistence);
	Assert::same($key, $storedKey);
	Assert::same([null, $applicationRequest], $storedValue);
});
