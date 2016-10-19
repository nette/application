<?php

/**
 * Test: Custom ErrorPresenter
 */

use Nette\Application;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


define('TEMP_DIR', __DIR__ . '/../tmp/' . lcg_value());
@mkdir(TEMP_DIR, 0777, TRUE); // @ - base directory may already exist
register_shutdown_function(function () {
	Tester\Helpers::purge(TEMP_DIR);
	rmdir(TEMP_DIR);
});


class TestPresenter extends Application\UI\Presenter
{

	public function actionDenied()
	{
		throw new Application\ForbiddenRequestException();
	}


	public function actionSuccess()
	{
		print 'Success';
		$this->terminate();
	}

}


class ErrorPresenter implements Application\IPresenter
{

	public function run(Application\Request $request)
	{
		$latest = $request->getParameter('request');

		if ($latest) {
			$output = 'Error: ' . $latest->getPresenterName() . ':' . $latest->getParameter('action');
			$output.= ' / ' . $request->getParameter('exception')->getCode();
		} else {
			$output = 'Error: NULL';
		}

		return new Application\Responses\TextResponse($output);
	}

}


function testRequest($url) {
	$configurator = new Nette\Configurator;
	$configurator->setDebugMode(FALSE);
	$configurator->setTempDirectory(TEMP_DIR);
	$container = $configurator->createContainer();

	$router = $container->getService('router');
	$router[] = new Application\Routers\Route('//www.%domain%/<presenter>/<action>/', [
		'presenter' => 'Test',
		'action' => 'default'
	]);

	$request = new Nette\Http\Request(new Nette\Http\UrlScript($url));
	$container->addService('httpRequest', $request);

	$application = $container->getByType('Nette\Application\Application');
	$application->errorPresenter = 'Error';

	ob_start();
	$application->run();
	return ob_get_clean();
}


Assert::same('Error: NULL', testRequest('http://aaa.example.com/'));				// no route matches
Assert::same('Error: Missing:default / 404', testRequest('http://www.example.com/missing/'));	// missing presenter
Assert::same('Error: Test:default / 404', testRequest('http://www.example.com/'));		// presenter found, template missing
Assert::same('Error: Test:denied / 403', testRequest('http://www.example.com/test/denied/'));

Assert::same('Success', testRequest('http://www.example.com/test/success/'));			// no error
