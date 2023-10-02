<?php

/**
 * Test: Nette\Application\UI\Presenter::link()
 */

declare(strict_types=1);

use Nette\Application;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class TestControl extends Application\UI\Control
{
	/** @persistent array */
	public $order = [];

	/** @persistent int */
	public $round = 0;


	public function handleClick($x, $y)
	{
	}


	public function loadState(array $params): void
	{
		if (isset($params['order'])) {
			$params['order'] = explode('.', $params['order']);
		}

		parent::loadState($params);
	}


	public function saveState(array &$params): void
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
	public $parr = [];

	/** @persistent */
	public $pbool = true;

	/** @persistent */
	public array $parrn;

	/** @persistent */
	public ?bool $pbooln = null;


	protected function startup(): void
	{
		parent::startup();
		$this['mycontrol'] = new TestControl;
		$this->invalidLinkMode = self::InvalidLinkTextual;

		// standard
		Assert::same("#error: Invalid destination ''.", $this->link(''));
		Assert::same('/index.php?action=params&presenter=Test', $this->link('params'));
		Assert::same(['pint' => null, 'parr' => null, 'pbool' => null, 'action' => 'params'], $this->getLastCreatedRequest()->getParameters());
		Assert::same('http://localhost/index.php?action=params&presenter=Test', $this->link('//params'));
		Assert::same('#error: Argument $arr passed to TestPresenter::actionParams() must be scalar, array given.', $this->link('params', [1, 2, 3, []]));
		Assert::same('/index.php?xx=1&action=params&presenter=Test', $this->link('params', ['xx' => 1, 'yy' => []]));
		Assert::same(['xx' => 1, 'yy' => [], 'pint' => null, 'parr' => null, 'pbool' => null, 'action' => 'params'], $this->getLastCreatedRequest()->getParameters());
		Assert::same('/index.php?sort%5By%5D%5Basc%5D=1&action=default&presenter=Test', $this->link('this', ['sort' => ['y' => ['asc' => true]]]));
		Assert::same(['sort' => ['y' => ['asc' => true]], 'pint' => null, 'parr' => null, 'pbool' => null, 'mycontrol-order' => null, 'mycontrol-round' => null, 'action' => 'default'], $this->getLastCreatedRequest()->getParameters());

		Assert::same("#error: Unable to pass parameters to action 'Test:product', missing corresponding method.", $this->link('product', 1));
		Assert::same('/index.php?a=1&action=product&presenter=Test', $this->link('product', ['a' => 1]));
		Assert::same('#error: Passed more parameters than method TestPresenter::actionParams() expects.', $this->link('params', 1, 2, 3, 4, 5));

		// special url
		Assert::same('/index.php?x=1&y=2&action=product&presenter=Test', $this->link('product?x=1&y=2'));
		Assert::same('/index.php?x=1&y=2&action=product&presenter=Test#fragment', $this->link('product?x=1&y=2#fragment'));

		// absolute
		Assert::same('http://localhost/index.php?x=1&y=2&action=product&presenter=Test#fragment', $this->link('//product?x=1&y=2#fragment'));
		$this->absoluteUrls = true;
		Assert::same('http://localhost/index.php?x=1&y=2&action=product&presenter=Test#fragment', $this->link('product?x=1&y=2#fragment'));
		Assert::same('http://localhost/index.php?x=1&y=2&action=product&presenter=Test#fragment', $this->link('//product?x=1&y=2#fragment'));
		$this->absoluteUrls = false;

		// persistent params
		Assert::same('/index.php?action=params&presenter=Test', $this->link('params', ['pint' => $this->pint, 'p' => '']));
		Assert::same('/index.php?pint=20&parr%5B0%5D=1&action=params&presenter=Test', $this->link('params', ['pint' => $this->pint * 2, 'pbool' => true, 'parr' => [1]]));
		Assert::same(['pint' => 20, 'pbool' => null, 'parr' => [1], 'action' => 'params'], $this->getLastCreatedRequest()->getParameters());
		Assert::same('/index.php?pint=1&pbool=0&action=params&presenter=Test', $this->link('params', ['pint' => true, 'pbool' => '0', 'parr' => []]));
		Assert::same('/index.php?pint=0&pbool=0&p=0&action=params&presenter=Test', $this->link('params', ['pint' => false, 'pbool' => false, 'p' => false, 'parr' => null]));
		Assert::same("#error: Value passed to persistent parameter 'pbool' in presenter Test must be bool, string given.", $this->link('this', ['p' => null, 'pbool' => 'a']));
		Assert::same("#error: Value passed to persistent parameter 'p' in presenter Test must be scalar, array given.", $this->link('this', ['p' => [1], 'pbool' => false]));
		Assert::same('/index.php?action=persistent&presenter=Test', $this->link('persistent'));

		Assert::same('/index.php?pbooln=1&parrn%5B0%5D=1&action=params&presenter=Test', $this->link('params', ['pbooln' => true, 'parrn' => [1]]));
		Assert::same(['pbooln' => true, 'parrn' => [1], 'pint' => null, 'parr' => null, 'pbool' => null, 'action' => 'params'], $this->getLastCreatedRequest()->getParameters());
		Assert::same('/index.php?pbooln=0&action=params&presenter=Test', $this->link('params', ['pbooln' => '0', 'parrn' => []]));
		Assert::same(['pbooln' => false, 'parrn' => [], 'pint' => null, 'parr' => null, 'pbool' => null, 'action' => 'params'], $this->getLastCreatedRequest()->getParameters());
		Assert::same("#error: Value passed to persistent parameter 'pbooln' in presenter Test must be ?bool, string given.", $this->link('params', ['pbooln' => 'a']));
		Assert::same("#error: Value passed to persistent parameter 'parrn' in presenter Test must be array, string given.", $this->link('params', ['parrn' => 'a']));

		// Other presenter & action link
		Assert::same('/index.php?action=product&presenter=Other', $this->link('Other:product', ['p' => $this->p]));
		Assert::same('/index.php?p=0&action=product&presenter=Other', $this->link('Other:product', ['p' => $this->p * 2]));
		Assert::same('/index.php?p=123&presenter=Nette%3AMicro', $this->link('Nette:Micro:', ['p' => 123]));
		Assert::same(['p' => 123], $this->getLastCreatedRequest()->getParameters());

		// signal link
		Assert::same('#error: Signal must be non-empty string.', $this->link('mycontrol:!'));
		Assert::same('/index.php?action=default&presenter=Test', $this->link('this', ['p' => $this->p]));
		Assert::same('/index.php?action=default&presenter=Test', $this->link('this!', ['p' => $this->p]));
		Assert::same('/index.php?action=default&do=signal&presenter=Test', $this->link('signal!', ['p' => $this->p]));
		Assert::same('/index.php?p=0&action=default&do=signal&presenter=Test', $this->link('signal!', [1, 'p' => $this->p * 2]));
		Assert::same('/index.php?y=2&action=default&do=signal&presenter=Test', $this->link('signal!', 1, 2));
		Assert::same(['x' => null, 'y' => 2, 'pint' => null, 'parr' => null, 'pbool' => null, 'mycontrol-order' => null, 'mycontrol-round' => null, 'action' => 'default', 'do' => 'signal'], $this->getLastCreatedRequest()->getParameters());

		// Component link
		Assert::same("#error: Invalid destination ''.", $this['mycontrol']->link('', 0, 1));
		Assert::same('/index.php?mycontrol-x=0&mycontrol-y=1&action=default&do=mycontrol-click&presenter=Test', $this['mycontrol']->link('click', 0, 1));
		Assert::same('/index.php?mycontrol-x=0a&mycontrol-y=1a&action=default&do=mycontrol-click&presenter=Test', $this['mycontrol']->link('click', '0a', '1a'));
		Assert::same('/index.php?mycontrol-x=1&action=default&do=mycontrol-click&presenter=Test', $this['mycontrol']->link('click', [1]));
		Assert::same('#error: Argument $x passed to TestControl::handleClick() must be scalar, array given.', $this['mycontrol']->link('click', [1], (object) [1]));
		Assert::same('/index.php?mycontrol-x=1&mycontrol-y=0&action=default&do=mycontrol-click&presenter=Test', $this['mycontrol']->link('click', true, false));
		Assert::same('/index.php?action=default&do=mycontrol-click&presenter=Test', $this['mycontrol']->link('click', null, ''));
		Assert::same('#error: Passed more parameters than method TestControl::handleClick() expects.', $this['mycontrol']->link('click', 1, 2, 3));
		Assert::same('http://localhost/index.php?mycontrol-x=1&mycontrol-round=1&action=default&presenter=Test#frag', $this['mycontrol']->link('//this?x=1&round=1#frag'));
		Assert::same('/index.php?mycontrol-x=1&mycontrol-y=2&action=default&do=mycontrol-click&presenter=Test', $this->link('mycontrol:click!', ['x' => 1, 'y' => 2, 'round' => 0]));
		Assert::same(['mycontrol-x' => 1, 'mycontrol-y' => 2, 'mycontrol-round' => null, 'mycontrol-order' => null, 'pint' => null, 'parr' => null, 'pbool' => null, 'action' => 'default', 'do' => 'mycontrol-click'], $this->getLastCreatedRequest()->getParameters());

		// Component link type checking
		Assert::same("#error: Value passed to persistent parameter 'order' in component 'mycontrol' must be array, int given.", $this['mycontrol']->link('click', ['order' => 1]));
		Assert::same("#error: Value passed to persistent parameter 'round' in component 'mycontrol' must be int, array given.", $this['mycontrol']->link('click', ['round' => []]));
		$this['mycontrol']->order = 1;
		Assert::same("#error: Value passed to persistent parameter 'order' in component 'mycontrol' must be array, int given.", $this['mycontrol']->link('click'));
		$this['mycontrol']->order = null;

		// type checking
		Assert::same('/index.php?action=params&presenter=Test', $this->link('params', []));
		Assert::same(['pint' => null, 'parr' => null, 'pbool' => null, 'action' => 'params'], $this->getLastCreatedRequest()->getParameters());
		Assert::same('/index.php?action=params&presenter=Test', $this->link('params', ['int' => null, 'bool' => null, 'str' => null, 'arr' => null]));
		Assert::same(['int' => null, 'bool' => null, 'str' => null, 'arr' => null, 'pint' => null, 'parr' => null, 'pbool' => null, 'action' => 'params'], $this->getLastCreatedRequest()->getParameters());
		Assert::same('/index.php?int=1&bool=1&str=abc&arr=1&action=params&presenter=Test', $this->link('params', ['int' => 1, 'bool' => true, 'str' => 'abc', 'arr' => '1']));
		Assert::same('/index.php?int=0&bool=0&action=params&presenter=Test', $this->link('params', ['int' => 0, 'bool' => false, 'str' => '', 'arr' => '']));
		Assert::same('/index.php?action=params&presenter=Test', $this->link('params', ['int' => new stdClass]));

		Assert::same('#error: Missing parameter $int required by TestPresenter::actionHints()', $this->link('hints', []));
		Assert::same('#error: Missing parameter $int required by TestPresenter::actionHints()', $this->link('hints', ['int' => null, 'bool' => null, 'str' => null, 'arr' => null]));
		Assert::same('/index.php?int=1&bool=1&str=abc&arr%5B0%5D=1&action=hints&presenter=Test', $this->link('hints', ['int' => '1', 'bool' => '1', 'str' => 'abc', 'arr' => [1]]));
		Assert::same('/index.php?int=0&bool=0&action=hints&presenter=Test', $this->link('hints', ['int' => 0, 'bool' => false, 'str' => '', 'arr' => []]));
		Assert::same('#error: Argument $int passed to TestPresenter::actionHints() must be int, string given.', $this->link('hints', ['int' => '']));
		Assert::same('#error: Argument $int passed to TestPresenter::actionHints() must be int, stdClass given.', $this->link('hints', ['int' => new stdClass]));
		Assert::same('#error: Argument $int passed to TestPresenter::actionHints() must be int, array given.', $this->link('hints', ['int' => []]));
		Assert::same('#error: Argument $bool passed to TestPresenter::actionHints() must be bool, string given.', $this->link('hints', ['int' => '1', 'bool' => '']));
		Assert::same('#error: Argument $arr passed to TestPresenter::actionHints() must be array, string given.', $this->link('hints', ['int' => '1', 'bool' => '1', 'str' => '', 'arr' => '']));

		Assert::same('/index.php?action=hintsNulls&presenter=Test', $this->link('hintsNulls', []));
		Assert::same('/index.php?action=hintsNulls&presenter=Test', $this->link('hintsNulls', ['int' => null, 'bool' => null, 'str' => null, 'arr' => null]));
		Assert::same('/index.php?int=1&bool=1&str=abc&arr%5B0%5D=1&action=hintsNulls&presenter=Test', $this->link('hintsNulls', ['int' => '1', 'bool' => '1', 'str' => 'abc', 'arr' => [1]]));
		Assert::same('/index.php?int=0&bool=0&action=hintsNulls&presenter=Test', $this->link('hintsNulls', ['int' => 0, 'bool' => false, 'str' => '', 'arr' => []]));
		Assert::same('#error: Argument $int passed to TestPresenter::actionHintsNulls() must be ?int, string given.', $this->link('hintsNulls', ['int' => '']));
		Assert::same('#error: Argument $int passed to TestPresenter::actionHintsNulls() must be ?int, stdClass given.', $this->link('hintsNulls', ['int' => new stdClass]));
		Assert::same('#error: Argument $int passed to TestPresenter::actionHintsNulls() must be ?int, array given.', $this->link('hintsNulls', ['int' => []]));
		Assert::same('#error: Argument $bool passed to TestPresenter::actionHintsNulls() must be ?bool, string given.', $this->link('hintsNulls', ['int' => '1', 'bool' => '']));
		Assert::same('#error: Argument $arr passed to TestPresenter::actionHintsNulls() must be ?array, string given.', $this->link('hintsNulls', ['int' => '1', 'bool' => '1', 'str' => '', 'arr' => '']));

		Assert::same('/index.php?action=hintsNullable&presenter=Test', $this->link('hintsNullable', []));
		Assert::same('/index.php?action=hintsNullable&presenter=Test', $this->link('hintsNullable', ['int' => null, 'bool' => null, 'str' => null, 'arr' => null]));
		Assert::same('/index.php?int=1&bool=1&str=abc&arr%5B0%5D=1&action=hintsNullable&presenter=Test', $this->link('hintsNullable', ['int' => '1', 'bool' => '1', 'str' => 'abc', 'arr' => [1]]));
		Assert::same('/index.php?int=0&bool=0&action=hintsNullable&presenter=Test', $this->link('hintsNullable', ['int' => 0, 'bool' => false, 'str' => '', 'arr' => []]));
		Assert::same('#error: Argument $int passed to TestPresenter::actionHintsNullable() must be ?int, string given.', $this->link('hintsNullable', ['int' => '']));
		Assert::same('#error: Argument $int passed to TestPresenter::actionHintsNullable() must be ?int, stdClass given.', $this->link('hintsNullable', ['int' => new stdClass]));
		Assert::same('#error: Argument $int passed to TestPresenter::actionHintsNullable() must be ?int, array given.', $this->link('hintsNullable', ['int' => []]));
		Assert::same('#error: Argument $bool passed to TestPresenter::actionHintsNullable() must be ?bool, string given.', $this->link('hintsNullable', ['int' => '1', 'bool' => '']));
		Assert::same('#error: Argument $arr passed to TestPresenter::actionHintsNullable() must be ?array, string given.', $this->link('hintsNullable', ['int' => '1', 'bool' => '1', 'str' => '', 'arr' => '']));

		Assert::same('/index.php?action=hintsDefaults&presenter=Test', $this->link('hintsDefaults', []));
		Assert::same('/index.php?action=hintsDefaults&presenter=Test', $this->link('hintsDefaults', ['int' => null, 'bool' => null, 'str' => null, 'arr' => null]));
		Assert::same('/index.php?int=1&bool=1&str=abc&arr%5B0%5D=1&action=hintsDefaults&presenter=Test', $this->link('hintsDefaults', ['int' => '1', 'bool' => '1', 'str' => 'abc', 'arr' => [1]]));
		Assert::same(['int' => 1, 'bool' => true, 'str' => 'abc', 'arr' => [1], 'pint' => null, 'parr' => null, 'pbool' => null, 'action' => 'hintsDefaults'], $this->getLastCreatedRequest()->getParameters());
		Assert::same('/index.php?action=hintsDefaults&presenter=Test', $this->link('hintsDefaults', ['int' => 0, 'bool' => false, 'str' => '', 'arr' => []]));
		Assert::same('#error: Argument $int passed to TestPresenter::actionHintsDefaults() must be int, string given.', $this->link('hintsDefaults', ['int' => '']));
		Assert::same('#error: Argument $int passed to TestPresenter::actionHintsDefaults() must be int, stdClass given.', $this->link('hintsDefaults', ['int' => new stdClass]));
		Assert::same('#error: Argument $int passed to TestPresenter::actionHintsDefaults() must be int, array given.', $this->link('hintsDefaults', ['int' => []]));
		Assert::same('#error: Argument $bool passed to TestPresenter::actionHintsDefaults() must be bool, string given.', $this->link('hintsDefaults', ['int' => '1', 'bool' => '']));
		Assert::same('#error: Argument $arr passed to TestPresenter::actionHintsDefaults() must be array, string given.', $this->link('hintsDefaults', ['int' => '1', 'bool' => '1', 'str' => '', 'arr' => '']));

		Assert::same('/index.php?action=defaults&presenter=Test', $this->link('defaults', []));
		Assert::same('/index.php?action=defaults&presenter=Test', $this->link('defaults', ['int' => null, 'bool' => null, 'str' => null, 'arr' => null]));
		Assert::same('/index.php?action=defaults&presenter=Test', $this->link('defaults', ['int' => '1', 'bool' => '1', 'str' => 'a', 'arr' => [1]]));
		Assert::same(['int' => null, 'bool' => null, 'str' => null, 'arr' => null, 'pint' => null, 'parr' => null, 'pbool' => null, 'action' => 'defaults'], $this->getLastCreatedRequest()->getParameters());
		Assert::same('/index.php?int=0&bool=0&str=&action=defaults&presenter=Test', $this->link('defaults', ['int' => 0, 'bool' => false, 'str' => '', 'arr' => []]));
		Assert::same('#error: Argument $int passed to TestPresenter::actionDefaults() must be int, string given.', $this->link('defaults', ['int' => '']));
		Assert::same('#error: Argument $int passed to TestPresenter::actionDefaults() must be int, array given.', $this->link('defaults', ['int' => []]));
		Assert::same('#error: Argument $int passed to TestPresenter::actionDefaults() must be int, stdClass given.', $this->link('defaults', ['int' => new stdClass]));
		Assert::same('#error: Argument $bool passed to TestPresenter::actionDefaults() must be bool, string given.', $this->link('defaults', ['int' => '1', 'bool' => '']));
		Assert::same('#error: Argument $arr passed to TestPresenter::actionDefaults() must be array, string given.', $this->link('defaults', ['int' => '1', 'bool' => '1', 'str' => '', 'arr' => '']));

		Assert::same('/index.php?action=objects&presenter=Test', $this->link('objects', ['req' => new stdClass, 'nullable' => new stdClass, 'opt' => new stdClass]));
		Assert::same('#error: Missing parameter $req required by TestPresenter::actionObjects()', $this->link('objects', []));
		Assert::same('#error: Missing parameter $req required by TestPresenter::actionObjects()', $this->link('objects', ['req' => null, 'nullable' => null, 'opt' => null]));
		Assert::same('#error: Argument $req passed to TestPresenter::actionObjects() must be stdClass, Exception given.', $this->link('objects', ['req' => new Exception, 'opt' => null]));
		Assert::same('#error: Argument $req passed to TestPresenter::actionObjects() must be stdClass, array given.', $this->link('objects', ['req' => []]));

		// silent invalid link mode
		$this->invalidLinkMode = self::InvalidLinkSilent;
		Assert::same('#', $this->link('params', ['p' => null, 'pbool' => 'a']));

		// warning invalid link mode
		$this->invalidLinkMode = self::InvalidLinkWarning;
		Assert::error(
			fn() => $this->link('params', ['p' => null, 'pbool' => 'a']),
			E_USER_WARNING,
			"Invalid link: Value passed to persistent parameter 'pbool' in presenter Test must be bool, string given.",
		);

		// exception invalid link mode
		$this->invalidLinkMode = self::InvalidLinkException;
		Assert::exception(
			fn() => $this->link('params', ['p' => null, 'pbool' => 'a']),
			Nette\Application\UI\InvalidLinkException::class,
			"Value passed to persistent parameter 'pbool' in presenter Test must be bool, string given.",
		);

		$this->p = null; // null in persistent parameter means default
		Assert::same('/index.php?action=params&presenter=Test', $this->link('params'));
		$this->terminate();
	}


	public function actionParams($int, $bool, $str, $arr)
	{
	}


	public function actionHints(int $int, bool $bool, string $str, array $arr)
	{
	}


	public function actionHintsNulls(?int $int = null, ?bool $bool = null, ?string $str = null, ?array $arr = null)
	{
	}


	public function actionHintsNullable(?int $int, ?bool $bool, ?string $str, ?array $arr)
	{
	}


	public function actionHintsDefaults(int $int = 0, bool $bool = false, string $str = '', array $arr = [])
	{
	}


	public function actionDefaults($int = 1, $bool = true, $str = 'a', $arr = [1])
	{
	}


	public function actionObjects(stdClass $req, ?stdClass $nullable, ?stdClass $opt = null)
	{
	}


	public function actionPersistent(int $pint)
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


$url = new Http\UrlScript('http://localhost/index.php', '/index.php');

$presenterFactory = Mockery::mock(Nette\Application\IPresenterFactory::class);
$presenterFactory->shouldReceive('getPresenterClass')
	->andReturnUsing(fn($presenter) => $presenter . 'Presenter');

$presenter = new TestPresenter;
$presenter->injectPrimary(
	new Http\Request($url),
	new Http\Response,
	$presenterFactory,
	new Application\Routers\SimpleRouter,
);

$presenter->invalidLinkMode = TestPresenter::InvalidLinkWarning;
$presenter->autoCanonicalize = false;

$request = new Application\Request('Test', Http\Request::Get, []);
$presenter->run($request);
