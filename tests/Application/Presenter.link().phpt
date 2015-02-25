<?php

/**
 * Test: Nette\Application\UI\Presenter::link()
 */

use Nette\Http,
	Nette\Application,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class TestControl extends Application\UI\Control
{
	/** @persistent array */
	public $order = array();

	/** @persistent int */
	public $round = 0;


	public function handleClick($x, $y)
	{
	}


	/**
	 * Loads params
	 * @param  array
	 * @return void
	 */
	public function loadState(array $params)
	{
		if (isset($params['order'])) {
			$params['order'] = explode('.', $params['order']);
		}
		parent::loadState($params);
	}


	/**
	 * Save params
	 * @param  array
	 * @return void
	 */
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
	public $var1 = 10;

	/** @persistent @var bool */
	public $ok = TRUE;

	/** @persistent @var bool */
	public $var2 = FALSE;


	protected function createTemplate($class = NULL)
	{
	}


	protected function startup()
	{
		parent::startup();
		$this['mycontrol'] = new TestControl;
		$this->invalidLinkMode = self::INVALID_LINK_TEXTUAL;

		// Presenter & action link
		Assert::same( '/index.php?action=product&presenter=Test', $this->link('product', array('var1' => $this->var1)) );
		Assert::same( '/index.php?var1=20&action=product&presenter=Test', $this->link('product', array('var1' => $this->var1 * 2, 'ok' => TRUE)) );
		Assert::same( '/index.php?var1=1&ok=0&action=product&presenter=Test', $this->link('product', array('var1' => TRUE, 'ok' => '0')) );
		Assert::same( "#error: Invalid value for persistent parameter 'ok' in 'Test', expected boolean.", $this->link('product', array('var1' => NULL, 'ok' => 'a')) );
		Assert::same( "#error: Invalid value for persistent parameter 'var1' in 'Test', expected integer.", $this->link('product', array('var1' => array(1), 'ok' => FALSE)) );
		Assert::same( "#error: Unable to pass parameters to action 'Test:product', missing corresponding method.", $this->link('product', 1, 2) );
		Assert::same( '/index.php?x=1&y=2&action=product&presenter=Test', $this->link('product', array('x' => 1, 'y' => 2)) );
		Assert::same( '/index.php?action=product&presenter=Test', $this->link('product') );
		Assert::same( '#error: Destination must be non-empty string.', $this->link('') );
		Assert::same( '/index.php?action=product&presenter=Test#fragment', $this->link('product#fragment') );
		Assert::same( 'http://localhost/index.php?action=product&presenter=Test#fragment', $this->link('//product#fragment') );

		// Other presenter & action link
		Assert::same( '/index.php?var1=10&action=product&presenter=Other', $this->link('Other:product', array('var1' => $this->var1)) );
		Assert::same( '/index.php?action=product&presenter=Other', $this->link('Other:product', array('var1' => $this->var1 * 2)) );
		Assert::same( '/index.php?var1=123&presenter=Nette%3AMicro', $this->link('Nette:Micro:', array('var1' => 123)) );

		// Presenter & signal link
		Assert::same( '/index.php?action=default&do=buy&presenter=Test', $this->link('buy!', array('var1' => $this->var1)) );
		Assert::same( '/index.php?var1=20&action=default&do=buy&presenter=Test', $this->link('buy!', array('var1' => $this->var1 * 2)) );
		Assert::same( '/index.php?y=2&action=default&do=buy&presenter=Test', $this->link('buy!', 1, 2) );
		Assert::same( '/index.php?y=2&bool=1&str=1&action=default&do=buy&presenter=Test', $this->link('buy!', '1', '2', TRUE, TRUE) );
		Assert::same( '/index.php?y=2&str=0&action=default&do=buy&presenter=Test', $this->link('buy!', '1', '2', FALSE, FALSE) );
		Assert::same( '/index.php?action=default&do=buy&presenter=Test', $this->link('buy!', array(1), (object) array(1)) );
		Assert::same( '/index.php?y=2&action=default&do=buy&presenter=Test', $this->link('buy!', array(1, 'y' => 2)) );
		Assert::same( '/index.php?y=2&action=default&do=buy&presenter=Test', $this->link('buy!', array('x' => 1, 'y' => 2, 'var1' => $this->var1)) );
		Assert::same( '#error: Signal must be non-empty string.', $this->link('!') );
		Assert::same( '/index.php?action=default&presenter=Test', $this->link('this', array('var1' => $this->var1)) );
		Assert::same( '/index.php?action=default&presenter=Test', $this->link('this!', array('var1' => $this->var1)) );
		Assert::same( '/index.php?sort%5By%5D%5Basc%5D=1&action=default&presenter=Test', $this->link('this', array('sort' => array('y' => array('asc' => TRUE)))) );

		// Presenter & signal link type checking
		Assert::same( "#error: Invalid value for parameter 'x' in method TestPresenter::handlebuy(), expected integer.", $this->link('buy!', array(array())) );
		Assert::same( "/index.php?action=default&do=buy&presenter=Test", $this->link('buy!', array(new stdClass)) );

		// Component link
		Assert::same( '#error: Signal must be non-empty string.', $this['mycontrol']->link('', 0, 1) );
		Assert::same( '/index.php?mycontrol-x=0&mycontrol-y=1&action=default&do=mycontrol-click&presenter=Test', $this['mycontrol']->link('click', 0, 1) );
		Assert::same( '/index.php?mycontrol-x=0a&mycontrol-y=1a&action=default&do=mycontrol-click&presenter=Test', $this['mycontrol']->link('click', '0a', '1a') );
		Assert::same( '/index.php?mycontrol-x=1&action=default&do=mycontrol-click&presenter=Test', $this['mycontrol']->link('click', array(1), (object) array(1)) );
		Assert::same( '/index.php?mycontrol-x=1&action=default&do=mycontrol-click&presenter=Test', $this['mycontrol']->link('click', TRUE, FALSE) );
		Assert::same( '/index.php?action=default&do=mycontrol-click&presenter=Test', $this['mycontrol']->link('click', NULL, '') );
		Assert::same( "#error: Passed more parameters than method TestControl::handleClick() expects.", $this['mycontrol']->link('click', 1, 2, 3) );
		Assert::same( '/index.php?mycontrol-x=1&mycontrol-y=2&action=default&do=mycontrol-click&presenter=Test', $this['mycontrol']->link('click!', array('x' => 1, 'y' => 2, 'round' => 0)) );
		Assert::same( '/index.php?mycontrol-x=1&mycontrol-round=1&action=default&do=mycontrol-click&presenter=Test', $this['mycontrol']->link('click', array('x' => 1, 'round' => 1)) );
		Assert::same( '/index.php?mycontrol-x=1&mycontrol-round=1&action=default&presenter=Test', $this['mycontrol']->link('this', array('x' => 1, 'round' => 1)) );
		Assert::same( '/index.php?mycontrol-x=1&mycontrol-round=1&action=default&presenter=Test#frag', $this['mycontrol']->link('this#frag', array('x' => 1, 'round' => 1)) );

		// Component link type checking
		Assert::same( "#error: Invalid value for persistent parameter 'order' in 'mycontrol', expected array.", $this['mycontrol']->link('click', array('order' => 1)) );
		Assert::same( "#error: Invalid value for persistent parameter 'round' in 'mycontrol', expected integer.", $this['mycontrol']->link('click', array('round' => array())) );
		$this['mycontrol']->order = 1;
		Assert::same( "#error: Invalid value for persistent parameter 'order' in 'mycontrol', expected array.", $this['mycontrol']->link('click') );
		$this['mycontrol']->order = NULL;

		// silent invalid link mode
		$this->invalidLinkMode = self::INVALID_LINK_SILENT;
		Assert::same('#', $this->link('product', array('var1' => null, 'ok' => 'a')));

		// warning invalid link mode
		$this->invalidLinkMode = self::INVALID_LINK_WARNING;
		$me = $this;
		Assert::error(function() use ($me) {
			Assert::same('#', $me->link('product', array('var1' => null, 'ok' => 'a')));
		}, E_USER_WARNING, "Invalid link: Invalid value for persistent parameter 'ok' in 'Test', expected boolean.");

		// exception invalid link mode
		$this->invalidLinkMode = self::INVALID_LINK_EXCEPTION;
		Assert::exception(function() use ($me) {
			$me->link('product', array('var1' => null, 'ok' => 'a'));
		}, 'Nette\Application\UI\InvalidLinkException', "Invalid value for persistent parameter 'ok' in 'Test', expected boolean.");
	}


	/**
	 * @view: default
	 */
	public function handleBuy($x = 1, $y = 1, $bool = FALSE, $str = '')
	{
	}

}


class OtherPresenter extends TestPresenter
{
	/** @persistent */
	public $var1 = 20;
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

$request = new Application\Request('Test', Http\Request::GET, array());
$presenter->run($request);
