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
use Nette\Application\Responses\ForwardResponse;
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


class InfinityForwardingPresenter implements Nette\Application\IPresenter
{
	public function run(Request $request)
	{
		return new ForwardResponse($request);
	}
}


class BadException extends Exception
{
}


class BadPresenter implements Nette\Application\IPresenter
{
	public function run(Request $request)
	{
		throw new BadException;
	}
}


class ErrorPresenter implements Nette\Application\IPresenter
{
	public $request;


	public function run(Request $request)
	{
		$this->request = $request;
	}
}


$httpRequest = Mockery::mock(Nette\Http\IRequest::class);
$httpResponse = Mockery::mock(Nette\Http\IResponse::class);
$httpResponse->shouldIgnoreMissing();


// no route without error presenter
Assert::exception(function () use ($httpRequest, $httpResponse) {
	$presenterFactory = Mockery::mock(IPresenterFactory::class);
	$router = Mockery::mock(IRouter::class);
	$router->shouldReceive('match');

	$app = new Application($presenterFactory, $router, $httpRequest, $httpResponse);
	$app->run();
}, BadRequestException::class, 'No route for HTTP request.');


// no route with error presenter
test(function () use ($httpRequest, $httpResponse) {
	$errorPresenter = new ErrorPresenter;

	$presenterFactory = Mockery::mock(IPresenterFactory::class);
	$presenterFactory->shouldReceive('createPresenter')->with('Error')->andReturn($errorPresenter);

	$router = Mockery::mock(IRouter::class);
	$router->shouldReceive('match');

	$app = new Application($presenterFactory, $router, $httpRequest, $httpResponse);
	$app->catchExceptions = true;
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
	$app->catchExceptions = true;
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
	$app->catchExceptions = true;
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
	$app->catchExceptions = true;
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
	$app->catchExceptions = true;
	$app->errorPresenter = 'Error';
	$app->run();

	$requests = $app->getRequests();
	Assert::count(1, $requests);
	Assert::same('GET', $requests[0]->getMethod());
	Assert::same('Good', $requests[0]->getPresenterName());

	Assert::equal($requests[0], $presenter->request);
	Assert::null($errorPresenter->request);
});


// error during onShutdown with catchException + errorPresenter
Assert::noError(function () use ($httpRequest, $httpResponse) {
	$presenter = new ErrorPresenter;

	$presenterFactory = Mockery::mock(IPresenterFactory::class);
	$presenterFactory->shouldReceive('createPresenter')->with('Error')->andReturn($presenter);

	$router = Mockery::mock(IRouter::class);

	$errors = [];

	$app = new Application($presenterFactory, $router, $httpRequest, $httpResponse);
	$app->onStartup[] = function () {
		throw new RuntimeException('Error at startup', 1);
	};
	$app->onShutdown[] = function () {
		throw new RuntimeException('Error at shutdown', 2);
	};
	$app->onError[] = function ($app, $e) use (&$errors) {
		$errors[] = $e;
	};
	$app->catchExceptions = TRUE;
	$app->errorPresenter = 'Error';

	Assert::exception(function () use ($app) {
		$app->run();
	}, RuntimeException::class, 'Error at shutdown');

	Assert::count(2, $errors);
	Assert::equal('Error at startup', $errors[0]->getMessage());
	Assert::equal('Error at shutdown', $errors[1]->getMessage());
});


// check maxLoop
Assert::noError(function () use ($httpRequest, $httpResponse) {
	$presenter = new InfinityForwardingPresenter;

	$presenterFactory = Mockery::mock(IPresenterFactory::class);
	$presenterFactory->shouldReceive('createPresenter')->with('Infinity')->andReturn($presenter);

	$router = Mockery::mock(IRouter::class);
	$router->shouldReceive('match')->andReturn(new Request('Infinity', 'GET'));

	$app = new Application($presenterFactory, $router, $httpRequest, $httpResponse);
	$app->catchExceptions = TRUE;
	$app->errorPresenter = 'Error';

	// Use default maxLoop
	$app1 = clone $app;
	Assert::exception(function () use ($app1) {
		$app1->run();
	}, ApplicationException::class, 'Too many loops detected in application life cycle.');

	Assert::count(21, $app1->getRequests());

	// Redefine maxLoop
	Application::$maxLoop = 2;
	$app2 = clone $app;
	Assert::exception(function () use ($app2) {
		$app2->run();
	}, ApplicationException::class, 'Too many loops detected in application life cycle.');

	Assert::count(3, $app2->getRequests());
});
