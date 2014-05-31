<?php

/**
 * Test: Nette\Application\RequestStorage
 *
 * @author     Martin Major
 */

use Nette\Http,
	Nette\Application,
	Nette\Security\Identity,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/mocks.php';


$url = 'https://www.example.com/my/address?with=parameter';

$httpRequest = new Http\Request(new Http\UrlScript($url));

$session = new MockSession;
$session->mockSection = new MockSessionSection;

$user = new MockUser;
$user->mockIdentity = new Identity(42);

$requestStorage = new Application\RequestStorage($httpRequest, $session, $user, new MockMessageStorage);

$applicationRequest = new Application\Request('Presenter', 'action', array('param' => 'value'));

$key = $requestStorage->storeRequest($applicationRequest);

// restore key

Assert::null($requestStorage->restoreRequest('bad_key'));

$redirect = $requestStorage->restoreRequest($key);
Assert::type('Nette\Application\Responses\RedirectResponse', $redirect);
Assert::contains($url, $redirect->getUrl());

// redirect to original URL

$httpRequest = new Http\Request(new Http\UrlScript($url), array(Application\RequestStorage::REQUEST_KEY => $key));

$application = new Application\Application(new MockPresenterFactory, new MockRouter, $httpRequest, new MockResponse, $requestStorage);

$applicationRequest->setFlag(Application\Request::RESTORED);
Assert::equal($applicationRequest, $application->createInitialRequest());
