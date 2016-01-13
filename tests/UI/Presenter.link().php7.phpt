<?php

/**
 * Test: Nette\Application\UI\Presenter::link()
 * @phpVersion 7
 */

use Nette\Http;
use Nette\Application;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class TestPresenter extends Application\UI\Presenter
{
	/** @persistent */
	public $var1 = 10;

	/** @persistent */
	public $var2;


	protected function createTemplate($class = NULL)
	{
	}


	protected function startup()
	{
		parent::startup();
		$this->invalidLinkMode = self::INVALID_LINK_TEXTUAL;

		Assert::same('/index.php?action=default&do=hint&presenter=Test', $this->link('hint!', ['var1' => $this->var1]));
		Assert::same('/index.php?var1=20&action=default&do=hint&presenter=Test', $this->link('hint!', ['var1' => $this->var1 * 2]));
		Assert::same('/index.php?y=2&action=default&do=hint&presenter=Test', $this->link('hint!', 1, 2));
		Assert::same('/index.php?y=2&bool=1&str=1&action=default&do=hint&presenter=Test', $this->link('hint!', '1', '2', TRUE, TRUE));
		Assert::same('/index.php?str=0&action=default&do=hint&presenter=Test', $this->link('hint!', '1', 0, FALSE, FALSE));
		Assert::same('/index.php?action=default&do=hint&presenter=Test', $this->link('hint!', ['str' => '', 'var2' => '']));
		Assert::same('/index.php?action=default&do=hint&presenter=Test', $this->link('hint!', [1]));
		Assert::same('#error: Argument $x passed to TestPresenter::handleHint() must be int, array given.', $this->link('hint!', [1], (object) [1]));
		Assert::same('/index.php?y=2&action=default&do=hint&presenter=Test', $this->link('hint!', [1, 'y' => 2]));
		Assert::same('/index.php?y=2&action=default&do=hint&presenter=Test', $this->link('hint!', ['x' => 1, 'y' => 2, 'var1' => $this->var1]));
		Assert::same('#error: Signal must be non-empty string.', $this->link('!'));
		Assert::same('/index.php?action=default&presenter=Test', $this->link('this', ['var1' => $this->var1]));
		Assert::same('/index.php?action=default&presenter=Test', $this->link('this!', ['var1' => $this->var1]));
		Assert::same('/index.php?sort%5By%5D%5Basc%5D=1&action=default&presenter=Test', $this->link('this', ['sort' => ['y' => ['asc' => TRUE]]]));

		// type checking
		Assert::same('#error: Argument $x passed to TestPresenter::handleHint() must be int, string given.', $this->link('hint!', 'x'));
		Assert::same('#error: Argument $bool passed to TestPresenter::handleHint() must be bool, integer given.', $this->link('hint!', 1, 2, 3));
		Assert::same('#error: Argument $x passed to TestPresenter::handleHint() must be int, array given.', $this->link('hint!', [[]]));
		Assert::same('/index.php?action=default&do=hint&presenter=Test', $this->link('hint!'));
		Assert::same('#error: Argument $x passed to TestPresenter::handleHint() must be int, stdClass given.', $this->link('hint!', [new stdClass]));

		// optional arguments
		Assert::same('/index.php?y=2&action=default&do=null&presenter=Test', $this->link('null!', 1, 2));
		Assert::same('/index.php?y=2&bool=1&str=1&action=default&do=null&presenter=Test', $this->link('null!', '1', '2', TRUE, TRUE));
		Assert::same('/index.php?y=0&bool=0&str=0&action=default&do=null&presenter=Test', $this->link('null!', '1', 0, FALSE, FALSE));
		Assert::same('/index.php?action=default&do=null&presenter=Test', $this->link('null!', ['str' => '', 'var2' => '']));
		Assert::same('/index.php?action=default&do=null&presenter=Test', $this->link('null!', [1]));
		Assert::same('#error: Argument $x passed to TestPresenter::handleNull() must be int, array given.', $this->link('null!', [1], (object) [1]));
		Assert::same('/index.php?y=2&action=default&do=null&presenter=Test', $this->link('null!', [1, 'y' => 2]));
		Assert::same('/index.php?y=2&action=default&do=null&presenter=Test', $this->link('null!', ['x' => 1, 'y' => 2, 'var1' => $this->var1]));
		Assert::same('#error: Argument $bool passed to TestPresenter::handleNull() must be bool, integer given.', $this->link('null!', 1, 2, 3));
		Assert::same('#error: Argument $x passed to TestPresenter::handleNull() must be int, array given.', $this->link('null!', [[]]));
		Assert::same('/index.php?action=default&do=null&presenter=Test', $this->link('null!'));
		Assert::same('#error: Argument $x passed to TestPresenter::handleNull() must be int, stdClass given.', $this->link('null!', [new stdClass]));
	}


	public function handleHint(int $x = 1, int $y, bool $bool, string $str)
	{
	}


	public function handleNull(int $x = 1, int $y = NULL, bool $bool = NULL, string $str = NULL)
	{
	}

}


class MockPresenterFactory extends Nette\Object implements Nette\Application\IPresenterFactory
{
	function getPresenterClass(& $name)
	{
		return str_replace(':', 'Module\\', $name) . 'Presenter';
	}

	function createPresenter($name)
	{}
}


$url = new Http\UrlScript('http://localhost/index.php');
$url->setScriptPath('/index.php');

$presenter = new TestPresenter;
$presenter->injectPrimary(
	NULL,
	new MockPresenterFactory,
	new Application\Routers\SimpleRouter,
	new Http\Request($url),
	new Http\Response
);

$presenter->invalidLinkMode = TestPresenter::INVALID_LINK_WARNING;
$presenter->autoCanonicalize = FALSE;

$request = new Application\Request('Test', Http\Request::GET, []);
$presenter->run($request);
