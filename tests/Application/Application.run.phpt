<?php

/**
 * Test: Application
 */

declare(strict_types=1);

use Nette\Application\Application;
use Nette\Application\BadRequestException;
use Nette\Application\IPresenterFactory;
use Nette\Application\IRouter;
use Nette\Application\Request;
use Nette\Application\IResponse;
use Nette\Application\Responses\TextResponse;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class GoodPresenter implements Nette\Application\IPresenter
{
	public $request;

	function run(Request $request): IResponse
	{
		$this->request = $request;
		return new TextResponse('');
	}
}


class BadException extends Exception
{
}


class BadPresenter implements Nette\Application\IPresenter
{
	function run(Request $request): IResponse
	{
		throw new BadException;
	}
}


class ErrorPresenter implements Nette\Application\IPresenter
{
	public $request;

	function run(Request $request): IResponse
	{
		$this->request = $request;
		return new TextResponse('');
	}
}


$httpRequest = Mockery::mock(Nette\Http\IRequest::class);
$httpResponse = Mockery::mock(Nette\Http\IResponse::class);
$httpResponse->shouldIgnoreMissing();


// no route without error presenter
Assert::exception(function () use ($httpRequest, $httpResponse) {
	$presenterFactory = Mockery::mock(IPresenterFactory::class);
	$router = Mockery::mock(IRouter::class);
	$router->shouldReceive('match')->andReturn(NULL);

	$app = new Application($presenterFactory, $router, $httpRequest, $httpResponse);
	$app->run();
}, BadRequestException::class, 'No route for HTTP request.');


// no route with error presenter
test(function () use ($httpRequest, $httpResponse) {
	$errorPresenter = new ErrorPresenter;

	$presenterFactory = Mockery::mock(IPresenterFactory::class);
	$presenterFactory->shouldReceive('createPresenter')->with('Error')->andReturn($errorPresenter);

	$router = Mockery::mock(IRouter::class);
	$router->shouldReceive('match')->andReturn(NULL);

	$app = new Application($presenterFactory, $router, $httpRequest, $httpResponse);
	$app->catchExceptions = TRUE;
	$app->errorPresenter = 'Error';
	$app->run();

	$requests = $app->getRequests();
	Assert::count(1, $requests);
	Assert::same(Request::FORWARD, $requests[0]->getMethod());
	Assert::same('Error', $requests[0]->getPresenterName());

	Assert::equal($requests[0], $errorPresenter->request);
	Assert::null($errorPresenter->request->getParameter('request'));
	Assert::type(BadRequestException::class, $errorPresenter->request->getParameter('exception'));
});


// route to error presenter
test(function () use ($httpRequest, $httpResponse) {
	$errorPresenter = new ErrorPresenter;

	$presenterFactory = Mockery::mock(IPresenterFactory::class);
	$presenterFactory->shouldReceive('createPresenter')->with('Error')->andReturn($errorPresenter);

	$router = Mockery::mock(IRouter::class);
	$router->shouldReceive('match')->andReturn(new Request('Error', 'GET'));

	$app = new Application($presenterFactory, $router, $httpRequest, $httpResponse);
	$app->catchExceptions = TRUE;
	$app->errorPresenter = 'Error';
	$app->run();

	$requests = $app->getRequests();
	Assert::count(2, $requests);
	Assert::same('GET', $requests[0]->getMethod());
	Assert::same('Error', $requests[0]->getPresenterName());
	Assert::same(Request::FORWARD, $requests[1]->getMethod());
	Assert::same('Error', $requests[1]->getPresenterName());

	Assert::equal($requests[1], $errorPresenter->request);
	Assert::equal($requests[0], $errorPresenter->request->getParameter('request'));
	Assert::type(BadRequestException::class, $errorPresenter->request->getParameter('exception'));
});


// missing presenter without error presenter
Assert::exception(function () use ($httpRequest, $httpResponse) {
	$presenterFactory = Mockery::mock(IPresenterFactory::class);
	$presenterFactory->shouldReceive('createPresenter')->with('Missing')->andThrow(Nette\Application\InvalidPresenterException::class);

	$router = Mockery::mock(IRouter::class);
	$router->shouldReceive('match')->andReturn(new Request('Missing', 'GET'));

	$app = new Application($presenterFactory, $router, $httpRequest, $httpResponse);
	$app->run();
}, BadRequestException::class);


// missing presenter with error presenter
test(function () use ($httpRequest, $httpResponse) {
	$errorPresenter = new ErrorPresenter;

	$presenterFactory = Mockery::mock(IPresenterFactory::class);
	$presenterFactory->shouldReceive('createPresenter')->with('Missing')->andThrow(Nette\Application\InvalidPresenterException::class);
	$presenterFactory->shouldReceive('createPresenter')->with('Error')->andReturn($errorPresenter);

	$router = Mockery::mock(IRouter::class);
	$router->shouldReceive('match')->andReturn(new Request('Missing', 'GET'));

	$app = new Application($presenterFactory, $router, $httpRequest, $httpResponse);
	$app->catchExceptions = TRUE;
	$app->errorPresenter = 'Error';
	$app->run();

	$requests = $app->getRequests();
	Assert::count(2, $requests);
	Assert::same('GET', $requests[0]->getMethod());
	Assert::same('Missing', $requests[0]->getPresenterName());
	Assert::same(Request::FORWARD, $requests[1]->getMethod());
	Assert::same('Error', $requests[1]->getPresenterName());

	Assert::equal($requests[1], $errorPresenter->request);
	Assert::equal($requests[0], $errorPresenter->request->getParameter('request'));
	Assert::type(BadRequestException::class, $errorPresenter->request->getParameter('exception'));
});


// presenter error without error presenter
Assert::exception(function () use ($httpRequest, $httpResponse) {
	$presenterFactory = Mockery::mock(IPresenterFactory::class);
	$presenterFactory->shouldReceive('createPresenter')->with('Bad')->andReturn(new BadPresenter);

	$router = Mockery::mock(IRouter::class);
	$router->shouldReceive('match')->andReturn(new Request('Bad', 'GET'));

	$app = new Application($presenterFactory, $router, $httpRequest, $httpResponse);
	$app->run();
}, BadException::class);


// presenter error with error presenter
test(function () use ($httpRequest, $httpResponse) {
	$errorPresenter = new ErrorPresenter;

	$presenterFactory = Mockery::mock(IPresenterFactory::class);
	$presenterFactory->shouldReceive('createPresenter')->with('Bad')->andReturn(new BadPresenter);
	$presenterFactory->shouldReceive('createPresenter')->with('Error')->andReturn($errorPresenter);

	$router = Mockery::mock(IRouter::class);
	$router->shouldReceive('match')->andReturn(new Request('Bad', 'GET'));

	$app = new Application($presenterFactory, $router, $httpRequest, $httpResponse);
	$app->catchExceptions = TRUE;
	$app->errorPresenter = 'Error';
	$app->run();

	$requests = $app->getRequests();
	Assert::count(2, $requests);
	Assert::same('GET', $requests[0]->getMethod());
	Assert::same('Bad', $requests[0]->getPresenterName());
	Assert::same(Request::FORWARD, $requests[1]->getMethod());
	Assert::same('Error', $requests[1]->getPresenterName());

	Assert::equal($requests[1], $errorPresenter->request);
	Assert::equal($requests[0], $errorPresenter->request->getParameter('request'));
	Assert::type(BadException::class, $errorPresenter->request->getParameter('exception'));
});


// no error without error presenter
Assert::noError(function () use ($httpRequest, $httpResponse) {
	$presenter = new GoodPresenter;

	$presenterFactory = Mockery::mock(IPresenterFactory::class);
	$presenterFactory->shouldReceive('createPresenter')->with('Good')->andReturn($presenter);

	$router = Mockery::mock(IRouter::class);
	$router->shouldReceive('match')->andReturn(new Request('Good', 'GET'));

	$app = new Application($presenterFactory, $router, $httpRequest, $httpResponse);
	$app->run();

	$requests = $app->getRequests();
	Assert::count(1, $requests);
	Assert::same('GET', $requests[0]->getMethod());
	Assert::same('Good', $requests[0]->getPresenterName());

	Assert::equal($requests[0], $presenter->request);
});


// no error with error presenter
Assert::noError(function () use ($httpRequest, $httpResponse) {
	$presenter = new GoodPresenter;
	$errorPresenter = new ErrorPresenter;

	$presenterFactory = Mockery::mock(IPresenterFactory::class);
	$presenterFactory->shouldReceive('createPresenter')->with('Good')->andReturn($presenter);
	$presenterFactory->shouldReceive('createPresenter')->with('Error')->andReturn($errorPresenter);

	$router = Mockery::mock(IRouter::class);
	$router->shouldReceive('match')->andReturn(new Request('Good', 'GET'));

	$app = new Application($presenterFactory, $router, $httpRequest, $httpResponse);
	$app->catchExceptions = TRUE;
	$app->errorPresenter = 'Error';
	$app->run();

	$requests = $app->getRequests();
	Assert::count(1, $requests);
	Assert::same('GET', $requests[0]->getMethod());
	Assert::same('Good', $requests[0]->getPresenterName());

	Assert::equal($requests[0], $presenter->request);
	Assert::null($errorPresenter->request);
});
