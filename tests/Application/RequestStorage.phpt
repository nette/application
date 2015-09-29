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

$session = new MockSession($httpRequest, new Http\Response);
$session->mockSection = new MockSessionSection;
$session->mockFlashSection = new MockSessionSection;

$user = new MockUser;
$user->mockIdentity = new Identity(42);

$requestStorage = new Application\RequestStorage($session, $user);

$applicationRequest = new Application\Request('Presenter', 'action', ['param' => 'value']);

$key = $requestStorage->store($applicationRequest, $httpRequest->getUrl());


// restore key
Assert::null($requestStorage->getUrl('bad_key'));

$redirect = $requestStorage->getUrl($key);
Assert::same($url . '&_fid=x', $redirect);


// redirect to original URL
$httpRequest = new Http\Request(new Http\UrlScript($redirect));

$application = new Application\Application(new MockPresenterFactory, new MockRouter, $httpRequest, new MockResponse, $requestStorage);

$applicationRequest->setFlag(Application\Request::RESTORED);
Assert::equal($applicationRequest, $application->createInitialRequest());
