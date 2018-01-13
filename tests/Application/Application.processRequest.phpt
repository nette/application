<?php

/**
 * Test: Application
 */

use Nette\Application\Application;
use Nette\Application\ApplicationException;
use Nette\Application\BadRequestException;
use Nette\Application\IPresenterFactory;
use Nette\Application\IRouter;
use Nette\Application\Request;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class GoodPresenter implements Nette\Application\IPresenter
{
	public $request;


	public function run(Request $request)
	{
		$this->request = $request;
	}
}

class ResponsePresenter implements Nette\Application\IPresenter
{
	public $request;

	/** @var SpecialResponseMock */
	public $response;


	public function run(Request $request)
	{
		$this->request = $request;
		$this->response = new SpecialResponseMock();
		return $this->response;
	}
}

class SpecialResponseMock implements Nette\Application\IResponse
{
	public $httpRequest;
	public $httpResponse;
	public $sendCalled;

	function send( Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse ) {
		$this->httpRequest = $httpRequest;
		$this->httpResponse = $httpResponse;
		$this->sendCalled = true;
	}
}

class ForwardPresenter implements Nette\Application\IPresenter
{
	public $request;


	public function run(Request $request)
	{
		$this->request = $request;
		$forwardRequest = new Request('Good', 'GET');

		return new \Nette\Application\Responses\ForwardResponse( $forwardRequest );
	}
}

class ForwardFirstPresenter implements Nette\Application\IPresenter
{
	public $request;


	public function run(Request $request)
	{
		$this->request = $request;
		$forwardRequest = new Request('ForwardSecond', 'GET');

		return new \Nette\Application\Responses\ForwardResponse( $forwardRequest );
	}
}

class ForwardSecondPresenter implements Nette\Application\IPresenter
{
	public $request;


	public function run(Request $request)
	{
		$this->request = $request;
		$forwardRequest = new Request('ForwardFirst', 'GET');

		return new \Nette\Application\Responses\ForwardResponse( $forwardRequest );
	}
}

class ApplicationCallableMock
{

	public $onRequestCalled;
	public $onPresenterCalled;
	public $onResponseCalled;

	public $onRequestParamApplication;
	public $onRequestParamRequest;

	public $onPresenterParamApplication;
	public $onPresenterParamPresenter;

	public $onResponseParamApplication;
	public $onResponseParamResponse;

	public function onRequest($application, $request)
	{
		$this->onRequestCalled = true;
		$this->onRequestParamApplication = $application;
		$this->onRequestParamRequest = $request;
	}

	public function onPresenter($application, $presenter)
	{
		$this->onPresenterCalled = true;
		$this->onPresenterParamApplication = $application;
		$this->onPresenterParamPresenter = $presenter;
	}

	public function onResponse($application, $response)
	{
		$this->onResponseCalled = true;
		$this->onResponseParamApplication = $application;
		$this->onResponseParamResponse = $response;
	}
}

$httpRequest = Mockery::mock(Nette\Http\IRequest::class);
$httpResponse = Mockery::mock(Nette\Http\IResponse::class);
$httpResponse->shouldIgnoreMissing();

// forward response (mutation test for processRequest without goto)
Assert::noError(function () use ($httpRequest, $httpResponse) {
	$forwardPresenter = new ForwardPresenter();
	$goodPresenter = new GoodPresenter;

	$presenterFactory = Mockery::mock(IPresenterFactory::class);
	$presenterFactory->shouldReceive('createPresenter')->with('Forward')->andReturn($forwardPresenter);
	$presenterFactory->shouldReceive('createPresenter')->with('Good')->andReturn($goodPresenter);

	$router = Mockery::mock(IRouter::class);
	$router->shouldReceive('match')->andReturn(new Request('Forward', 'GET'));

	$app = new Application($presenterFactory, $router, $httpRequest, $httpResponse);
	$app->run();

	$requests = $app->getRequests();
	Assert::count(2, $requests);
	Assert::same('GET', $requests[0]->getMethod());
	Assert::same('Forward', $requests[0]->getPresenterName());

	Assert::same('GET', $requests[1]->getMethod());
	Assert::same('Good', $requests[1]->getPresenterName());

	Assert::equal($requests[0], $forwardPresenter->request);
});

// processRequest maxLoop (mutation test for processRequest without goto)
Assert::exception(function () use ($httpRequest, $httpResponse) {
	$forwardFirst = new ForwardFirstPresenter();
	$forwardSecond = new ForwardSecondPresenter();

	$presenterFactory = Mockery::mock(IPresenterFactory::class);
	$presenterFactory->shouldReceive('createPresenter')->with('ForwardFirst')->andReturn($forwardFirst);
	$presenterFactory->shouldReceive('createPresenter')->with('ForwardSecond')->andReturn($forwardSecond);

	$router = Mockery::mock(IRouter::class);
	$router->shouldReceive('match')->andReturn(new Request('ForwardFirst', 'GET'));

	$app = new Application($presenterFactory, $router, $httpRequest, $httpResponse);
	$app->run();

}, ApplicationException::class, 'Too many loops detected in application life cycle.');

// processRequest call onRequest and onPresenter and onResponse (mutation test for processRequest without goto)
Assert::noError(function () use ($httpRequest, $httpResponse) {
	$presenter = new ResponsePresenter;

	$presenterFactory = Mockery::mock(IPresenterFactory::class);
	$presenterFactory->shouldReceive('createPresenter')->with('Response')->andReturn($presenter);

	$router = Mockery::mock(IRouter::class);
	$request = new Request( 'Response', 'GET' );
	$router->shouldReceive('match')->andReturn( $request );

	$callableMock = new ApplicationCallableMock();

	$app = new Application($presenterFactory, $router, $httpRequest, $httpResponse);

	$app->onRequest[] = array($callableMock, 'onRequest');
	$app->onPresenter[] = array($callableMock, 'onPresenter');
	$app->onResponse[] = array($callableMock, 'onResponse');

	$app->run();


	Assert::true($callableMock->onRequestCalled);
	Assert::equal($callableMock->onRequestParamApplication, $app);
	Assert::equal($callableMock->onRequestParamRequest, $request);

	Assert::true($callableMock->onPresenterCalled);
	Assert::equal($callableMock->onPresenterParamApplication, $app);
	Assert::equal($callableMock->onPresenterParamPresenter, $presenter);

	Assert::true($callableMock->onResponseCalled);
	Assert::equal($callableMock->onResponseParamApplication, $app);
	Assert::equal($callableMock->onResponseParamResponse, $presenter->response);


});


// processRequest call onRequest and onPresenter (mutation test for processRequest without goto)
Assert::noError(function () use ($httpRequest, $httpResponse) {
	$presenter = new ResponsePresenter;

	$presenterFactory = Mockery::mock(IPresenterFactory::class);
	$presenterFactory->shouldReceive('createPresenter')->with('Response')->andReturn($presenter);

	$router = Mockery::mock(IRouter::class);
	$request = new Request( 'Response', 'GET' );
	$router->shouldReceive('match')->andReturn( $request );


	$app = new Application($presenterFactory, $router, $httpRequest, $httpResponse);

	$app->run();

	Assert::true($presenter->response->sendCalled);
	Assert::equal($httpRequest, $presenter->response->httpRequest);
	Assert::equal($httpResponse, $presenter->response->httpResponse);

});