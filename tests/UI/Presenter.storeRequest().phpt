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


test('request storage with user context', function () {
	$testedKeyExistence = $storedKey = $storedValue = $testExpiration = $testExpirationVariables = null;

	$sessionSectionMock = Mockery::mock(Nette\Http\SessionSection::class);
	$sessionSectionMock->shouldReceive('setExpiration')
		->andReturnUsing(function ($expiration, $variables = null) use (&$testExpiration, &$testExpirationVariables, $sessionSectionMock) {
			$testExpiration = $expiration;
			$testExpirationVariables = $variables;
			return $sessionSectionMock;
		});

	$sessionSectionMock->shouldReceive('get')
		->andReturnUsing(function ($name) use (&$testedKeyExistence) {
			$testedKeyExistence = $name;
			return false;
		});

	$sessionSectionMock->shouldReceive('set')
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


test('request storage without user context', function () {
	$testedKeyExistence = $storedKey = $storedValue = $testExpiration = $testExpirationVariables = null;

	$sessionSectionMock = Mockery::mock(Nette\Http\SessionSection::class);
	$sessionSectionMock->shouldReceive('setExpiration')
		->andReturnUsing(function ($expiration, $variables = null) use (&$testExpiration, &$testExpirationVariables, $sessionSectionMock) {
			$testExpiration = $expiration;
			$testExpirationVariables = $variables;
			return $sessionSectionMock;
		});

	$sessionSectionMock->shouldReceive('get')
		->andReturnUsing(function ($name) use (&$testedKeyExistence) {
			$testedKeyExistence = $name;
			return false;
		});

	$sessionSectionMock->shouldReceive('set')
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


test('restoreRequest() restores stored request', function () {
	$storedData = [
		'test_id',
		new Application\Request('Test', 'POST', ['action' => 'edit', 'id' => 5], ['name' => 'John']),
	];

	$sessionSection = Mockery::mock(Http\SessionSection::class);
	$sessionSection->shouldReceive('get')->with('abc123')->andReturn($storedData);
	$sessionSection->shouldReceive('remove')->with('abc123')->once();

	$session = Mockery::mock(Http\Session::class);
	$session->shouldReceive('getSection')->andReturn($sessionSection);

	$user = Mockery::mock(Nette\Security\User::class);
	$user->shouldReceive('getId')->andReturn('test_id');

	$presenter = new TestPresenter;
	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript),
		new Http\Response,
		new Application\PresenterFactory,
		new Application\Routers\SimpleRouter,
		$session,
		$user,
	);

	$presenter->autoCanonicalize = false;
	$presenter->run(new Application\Request('Test', 'GET', ['action' => 'default']));

	Assert::exception(
		fn() => $presenter->restoreRequest('abc123'),
		Application\AbortException::class,
	);

	// Verified that get/remove were called (via Mockery expectations)
});


test('restoreRequest() ignores request with wrong user', function () {
	$storedData = [
		'different_user',
		new Application\Request('Test', 'POST', ['action' => 'edit']),
	];

	$sessionSection = Mockery::mock(Http\SessionSection::class);
	$sessionSection->shouldReceive('get')->with('abc123')->andReturn($storedData);
	$sessionSection->shouldReceive('remove')->never();

	$session = Mockery::mock(Http\Session::class);
	$session->shouldReceive('getSection')->andReturn($sessionSection);

	$user = Mockery::mock(Nette\Security\User::class);
	$user->shouldReceive('getId')->andReturn('test_id');

	$presenter = new TestPresenter;
	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript),
		new Http\Response,
		null,
		new Application\Routers\SimpleRouter,
		$session,
		$user,
	);

	$presenter->autoCanonicalize = false;
	$presenter->run(new Application\Request('Test', 'GET', ['action' => 'default']));

	$presenter->restoreRequest('abc123');

	// Request should not be restored due to user mismatch
});


test('restoreRequest() handles missing request', function () {
	$sessionSection = Mockery::mock(Http\SessionSection::class);
	$sessionSection->shouldReceive('get')->with('nonexistent')->andReturn(null);
	$sessionSection->shouldReceive('remove')->never();

	$session = Mockery::mock(Http\Session::class);
	$session->shouldReceive('getSection')->andReturn($sessionSection);

	$presenter = new TestPresenter;
	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript),
		new Http\Response,
		null,
		new Application\Routers\SimpleRouter,
		$session,
	);

	$presenter->autoCanonicalize = false;
	$presenter->run(new Application\Request('Test', 'GET', ['action' => 'default']));

	$presenter->restoreRequest('nonexistent');

	// Should handle gracefully without error
});


test('restoreRequest() accepts request without user when stored without user', function () {
	$storedData = [
		null,
		new Application\Request('Test', 'POST', ['action' => 'edit']),
	];

	$sessionSection = Mockery::mock(Http\SessionSection::class);
	$sessionSection->shouldReceive('get')->with('abc123')->andReturn($storedData);
	$sessionSection->shouldReceive('remove')->with('abc123')->once();

	$session = Mockery::mock(Http\Session::class);
	$session->shouldReceive('getSection')->andReturn($sessionSection);

	$user = Mockery::mock(Nette\Security\User::class);
	$user->shouldReceive('getId')->andReturn('test_id');

	$presenter = new TestPresenter;
	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript),
		new Http\Response,
		new Application\PresenterFactory,
		new Application\Routers\SimpleRouter,
		$session,
		$user,
	);

	$presenter->autoCanonicalize = false;
	$presenter->run(new Application\Request('Test', 'GET', ['action' => 'default']));

	Assert::exception(
		fn() => $presenter->restoreRequest('abc123'),
		Application\AbortException::class,
	);

	// Request stored without user (null) should be restorable by any user
});
