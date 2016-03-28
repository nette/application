<?php

/**
 * Test: Nette\Application\UI\Presenter::link()
 */

use Nette\Http;
use Nette\Application;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/MockPresenterFactory.php';


class TestControl extends Application\UI\Control
{
	/** @persistent array */
	public $order = array();

	/** @persistent int */
	public $round = 0;


	public function handleClick($x, $y)
	{
	}

	public function loadState(array $params)
	{
		if (isset($params['order'])) {
			$params['order'] = explode('.', $params['order']);
		}
		parent::loadState($params);
	}

	public function saveState(array & $params)
	{
		parent::saveState($params);
		if (isset($params['order'])) {
			$params['order'] = implode('.', $params['order']);
		}
	}

}


class TestPresenter extends Application\UI\Presenter
{
	/** @persistent */
	public $p;

	/** @persistent */
	public $pint = 10;

	/** @persistent */
	public $parr = array();

	/** @persistent */
	public $pbool = TRUE;


	protected function createTemplate($class = NULL)
	{
	}


	protected function startup()
	{
		parent::startup();
		$this['mycontrol'] = new TestControl;
		$this->invalidLinkMode = self::INVALID_LINK_TEXTUAL;

		// standard
		Assert::same('#error: Destination must be non-empty string.', $this->link(''));
		Assert::same('/index.php?action=params&presenter=Test', $this->link('params'));
		Assert::same(array('pint' => NULL, 'parr' => NULL, 'pbool' => NULL, 'action' => 'params'), $this->getLastCreatedRequest()->getParameters());
		Assert::same('http://localhost/index.php?action=params&presenter=Test', $this->link('//params'));
		Assert::same('#error: Argument $arr passed to TestPresenter::actionParams() must be scalar, array given.', $this->link('params', array(1, 2, 3, array())));
		Assert::same('/index.php?xx=1&action=params&presenter=Test', $this->link('params', array('xx' => 1, 'yy' => array())));
		Assert::same(array('xx' => 1, 'yy' => array(), 'pint' => NULL, 'parr' => NULL, 'pbool' => NULL, 'action' => 'params'), $this->getLastCreatedRequest()->getParameters());
		Assert::same('/index.php?sort%5By%5D%5Basc%5D=1&action=default&presenter=Test', $this->link('this', array('sort' => array('y' => array('asc' => TRUE)))));
		Assert::same(array('sort' => array('y' => array('asc' => TRUE)), 'pint' => NULL, 'parr' => NULL, 'pbool' => NULL, 'mycontrol-order' => NULL, 'mycontrol-round' => NULL, 'action' => 'default'), $this->getLastCreatedRequest()->getParameters());

		Assert::same("#error: Unable to pass parameters to action 'Test:product', missing corresponding method.", $this->link('product', 1));
		Assert::same('/index.php?a=1&action=product&presenter=Test', $this->link('product', array('a' => 1)));
		Assert::same('#error: Passed more parameters than method TestPresenter::actionParams() expects.', $this->link('params', 1, 2, 3, 4, 5));

		// special url
		Assert::same('/index.php?x=1&y=2&action=product&presenter=Test', $this->link('product?x=1&y=2'));
		Assert::same('/index.php?x=1&y=2&action=product&presenter=Test#fragment', $this->link('product?x=1&y=2#fragment'));
		Assert::same('http://localhost/index.php?x=1&y=2&action=product&presenter=Test#fragment', $this->link('//product?x=1&y=2#fragment'));

		// persistent params
		Assert::same('/index.php?action=params&presenter=Test', $this->link('params', array('pint' => $this->pint, 'p' => '')));
		Assert::same('/index.php?pint=20&parr%5B0%5D=1&action=params&presenter=Test', $this->link('params', array('pint' => $this->pint * 2, 'pbool' => TRUE, 'parr' => array(1))));
		Assert::same(array('pint' => 20, 'pbool' => NULL, 'parr' => array(1), 'action' => 'params'), $this->getLastCreatedRequest()->getParameters());
		Assert::same('/index.php?pint=1&pbool=0&action=params&presenter=Test', $this->link('params', array('pint' => TRUE, 'pbool' => '0', 'parr' => array())));
		Assert::same('/index.php?pint=0&pbool=0&action=params&presenter=Test', $this->link('params', array('pint' => FALSE, 'pbool' => FALSE, 'p' => FALSE, 'parr' => NULL)));
		Assert::same("#error: Value passed to persistent parameter 'pbool' in presenter Test must be boolean, string given.", $this->link('this', array('p' => NULL, 'pbool' => 'a')));
		Assert::same("#error: Value passed to persistent parameter 'p' in presenter Test must be scalar, array given.", $this->link('this', array('p' => array(1), 'pbool' => FALSE)));

		// Other presenter & action link
		Assert::same('/index.php?action=product&presenter=Other', $this->link('Other:product', array('p' => $this->p)));
		Assert::same('/index.php?p=0&action=product&presenter=Other', $this->link('Other:product', array('p' => $this->p * 2)));
		Assert::same('/index.php?p=123&presenter=Nette%3AMicro', $this->link('Nette:Micro:', array('p' => 123)));
		Assert::same(array('p' => 123), $this->getLastCreatedRequest()->getParameters());

		// signal link
		Assert::same('#error: Signal must be non-empty string.', $this->link('!'));
		Assert::same('/index.php?action=default&presenter=Test', $this->link('this', array('p' => $this->p)));
		Assert::same('/index.php?action=default&presenter=Test', $this->link('this!', array('p' => $this->p)));
		Assert::same('/index.php?action=default&do=signal&presenter=Test', $this->link('signal!', array('p' => $this->p)));
		Assert::same('/index.php?p=0&action=default&do=signal&presenter=Test', $this->link('signal!', array(1, 'p' => $this->p * 2)));
		Assert::same('/index.php?y=2&action=default&do=signal&presenter=Test', $this->link('signal!', 1, 2));
		Assert::same(array('x' => NULL, 'y' => 2, 'pint' => NULL, 'parr' => NULL, 'pbool' => NULL, 'mycontrol-order' => NULL, 'mycontrol-round' => NULL, 'action' => 'default', 'do' => 'signal'), $this->getLastCreatedRequest()->getParameters());

		// Component link
		Assert::same('#error: Signal must be non-empty string.', $this['mycontrol']->link('', 0, 1));
		Assert::same('/index.php?mycontrol-x=0&mycontrol-y=1&action=default&do=mycontrol-click&presenter=Test', $this['mycontrol']->link('click', 0, 1));
		Assert::same('/index.php?mycontrol-x=0a&mycontrol-y=1a&action=default&do=mycontrol-click&presenter=Test', $this['mycontrol']->link('click', '0a', '1a'));
		Assert::same('/index.php?mycontrol-x=1&action=default&do=mycontrol-click&presenter=Test', $this['mycontrol']->link('click', array(1)));
		Assert::same('/index.php?mycontrol-x=1&action=default&do=mycontrol-click&presenter=Test', $this['mycontrol']->link('click', array(1), (object) array(1)));
		Assert::same('/index.php?mycontrol-x=1&action=default&do=mycontrol-click&presenter=Test', $this['mycontrol']->link('click', TRUE, FALSE));
		Assert::same('/index.php?action=default&do=mycontrol-click&presenter=Test', $this['mycontrol']->link('click', NULL, ''));
		Assert::same('#error: Passed more parameters than method TestControl::handleClick() expects.', $this['mycontrol']->link('click', 1, 2, 3));
		Assert::same('http://localhost/index.php?mycontrol-x=1&mycontrol-round=1&action=default&presenter=Test#frag', $this['mycontrol']->link('//this?x=1&round=1#frag'));
		Assert::same('/index.php?mycontrol-x=1&mycontrol-y=2&action=default&do=mycontrol-click&presenter=Test', $this->link('mycontrol:click!', array('x' => 1, 'y' => 2, 'round' => 0)));
		Assert::same(array('mycontrol-x' => 1, 'mycontrol-y' => 2, 'mycontrol-round' => NULL, 'mycontrol-order' => NULL, 'pint' => NULL, 'parr' => NULL, 'pbool' => NULL, 'action' => 'default', 'do' => 'mycontrol-click'), $this->getLastCreatedRequest()->getParameters());

		// Component link type checking
		Assert::same("#error: Value passed to persistent parameter 'order' in component 'mycontrol' must be array, integer given.", $this['mycontrol']->link('click', array('order' => 1)));
		Assert::same("#error: Value passed to persistent parameter 'round' in component 'mycontrol' must be integer, array given.", $this['mycontrol']->link('click', array('round' => array())));
		$this['mycontrol']->order = 1;
		Assert::same("#error: Value passed to persistent parameter 'order' in component 'mycontrol' must be array, integer given.", $this['mycontrol']->link('click'));
		$this['mycontrol']->order = NULL;

		// type checking
		Assert::same('/index.php?action=params&presenter=Test', $this->link('params', array()));
		Assert::same(array('pint' => NULL, 'parr' => NULL, 'pbool' => NULL, 'action' => 'params'), $this->getLastCreatedRequest()->getParameters());
		Assert::same('/index.php?action=params&presenter=Test', $this->link('params', array('int' => NULL, 'bool' => NULL, 'str' => NULL, 'arr' => NULL)));
		Assert::same(array('int' => NULL, 'bool' => NULL, 'str' => NULL, 'arr' => NULL, 'pint' => NULL, 'parr' => NULL, 'pbool' => NULL, 'action' => 'params'), $this->getLastCreatedRequest()->getParameters());
		Assert::same('/index.php?int=1&bool=1&str=abc&arr=1&action=params&presenter=Test', $this->link('params', array('int' => 1, 'bool' => TRUE, 'str' => 'abc', 'arr' => '1')));
		Assert::same('/index.php?int=0&action=params&presenter=Test', $this->link('params', array('int' => 0, 'bool' => FALSE, 'str' => '', 'arr' => '')));
		Assert::same('/index.php?action=params&presenter=Test', $this->link('params', array('int' => new stdClass)));

		Assert::same('/index.php?action=defaults&presenter=Test', $this->link('defaults', array()));
		Assert::same('/index.php?action=defaults&presenter=Test', $this->link('defaults', array('int' => NULL, 'bool' => NULL, 'str' => NULL, 'arr' => NULL)));
		Assert::same('/index.php?action=defaults&presenter=Test', $this->link('defaults', array('int' => '1', 'bool' => '1', 'str' => 'a', 'arr' => array(1))));
		Assert::same(array('int' => NULL, 'bool' => NULL, 'str' => NULL, 'arr' => NULL, 'pint' => NULL, 'parr' => NULL, 'pbool' => NULL, 'action' => 'defaults'), $this->getLastCreatedRequest()->getParameters());
		Assert::same('/index.php?int=0&bool=0&str=&action=defaults&presenter=Test', $this->link('defaults', array('int' => 0, 'bool' => FALSE, 'str' => '', 'arr' => array())));
		Assert::same('#error: Argument $int passed to TestPresenter::actionDefaults() must be integer, string given.', $this->link('defaults', array('int' => '')));
		Assert::same('#error: Argument $int passed to TestPresenter::actionDefaults() must be integer, array given.', $this->link('defaults', array('int' => array())));
		Assert::same('/index.php?action=defaults&presenter=Test', $this->link('defaults', array('int' => new stdClass)));
		Assert::same('#error: Argument $bool passed to TestPresenter::actionDefaults() must be boolean, string given.', $this->link('defaults', array('int' => '1', 'bool' => '')));
		Assert::same('#error: Argument $arr passed to TestPresenter::actionDefaults() must be array, string given.', $this->link('defaults', array('int' => '1', 'bool' => '1', 'str' => '', 'arr' => '')));

		Assert::same('/index.php?action=objects&presenter=Test', $this->link('objects', array('req' => new stdClass, 'opt' => new stdClass)));
		Assert::same('/index.php?action=objects&presenter=Test', $this->link('objects', array()));
		Assert::same('/index.php?action=objects&presenter=Test', $this->link('objects', array('req' => NULL, 'opt' => NULL)));
		Assert::same('/index.php?action=objects&presenter=Test', $this->link('objects', array('req' => new Exception, 'opt' => NULL)));
		Assert::same('#error: Argument $req passed to TestPresenter::actionObjects() must be stdClass, array given.', $this->link('objects', array('req' => array())));

		// silent invalid link mode
		$this->invalidLinkMode = self::INVALID_LINK_SILENT;
		Assert::same('#', $this->link('params', array('p' => NULL, 'pbool' => 'a')));

		// warning invalid link mode
		$this->invalidLinkMode = self::INVALID_LINK_WARNING;
		$me = $this;
		Assert::error(function () use ($me) {
			$me->link('params', array('p' => NULL, 'pbool' => 'a'));
		}, E_USER_WARNING, "Invalid link: Value passed to persistent parameter 'pbool' in presenter Test must be boolean, string given.");

		// exception invalid link mode
		$this->invalidLinkMode = self::INVALID_LINK_EXCEPTION;
		Assert::error(function () use ($me) {
			$me->link('params', array('p' => NULL, 'pbool' => 'a'));
		}, 'Nette\Application\UI\InvalidLinkException', "Value passed to persistent parameter 'pbool' in presenter Test must be boolean, string given.");

		$this->p = NULL; // NULL in persistent parameter means default
		Assert::same('/index.php?action=params&presenter=Test', $this->link('params'));
	}


	public function actionParams($int, $bool, $str, $arr)
	{
	}

	public function actionDefaults($int = 1, $bool = TRUE, $str = 'a', $arr = array(1))
	{
	}

	public function actionObjects(stdClass $req, stdClass $opt = NULL)
	{
	}

	public function handleSignal($x = 1, $y = 1)
	{
	}
}


class OtherPresenter extends TestPresenter
{
	/** @persistent */
	public $p = 20;
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

$request = new Application\Request('Test', Http\Request::GET, array());
$presenter->run($request);
