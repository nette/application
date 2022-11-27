<?php

/**
 * Test: Nette\Application\UI\Presenter::link() and persistent parameters
 */

declare(strict_types=1);

use Nette\Application;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


trait PersistentParam1
{
	/** @persistent */
	public $t1;
}

trait PersistentParam2A
{
	/** @persistent */
	public $t2;
}

trait PersistentParam2B
{
	use PersistentParam2A;
}

trait PersistentParam3
{
	/** @persistent */
	public $t3;
}

class BasePresenter extends Application\UI\Presenter
{
	use PersistentParam1;

	/** @persistent */
	public $p1;
}


class TestPresenter extends BasePresenter
{
	use PersistentParam2B;

	/** @persistent */
	public $p2;


	protected function startup(): void
	{
		parent::startup();

		$this->p1 = 1;
		$this->p2 = 2;
		$this->t1 = 3;
		$this->t2 = 4;
		Assert::same('/index.php?p2=2&p1=1&t1=3&t2=4&action=default&presenter=Test', $this->link('this'));
		Assert::same('/index.php?p1=1&t1=3&action=default&presenter=Second', $this->link('Second:'));
		Assert::same('/index.php?p1=1&t1=3&t2=4&action=default&presenter=Third', $this->link('Third:'));

		$this->p1 = 20;
		Assert::same('/index.php?t1=3&action=default&presenter=Second', $this->link('Second:'));

		$this->p1 = null; // means default
		Assert::same('/index.php?t1=3&action=default&presenter=Second', $this->link('Second:'));

		$this->terminate();
	}
}


class SecondPresenter extends BasePresenter
{
	use PersistentParam3;

	/** @persistent */
	public $p1 = 20;

	/** @persistent */
	public $p3;
}


class ThirdPresenter extends BasePresenter
{
	use PersistentParam2A;
}


class FourthPresenter extends BasePresenter
{
	#[Application\Attributes\Persistent]
	public $p1;
}


Assert::same([
	'p1' => ['def' => null, 'type' => 'scalar', 'since' => 'BasePresenter'],
	't1' => ['def' => null, 'type' => 'scalar', 'since' => 'PersistentParam1'],
], BasePresenter::getReflection()->getPersistentParams());

Assert::same([
	'p2' => ['def' => null, 'type' => 'scalar', 'since' => 'TestPresenter'],
	'p1' => ['def' => null, 'type' => 'scalar', 'since' => 'BasePresenter'],
	't1' => ['def' => null, 'type' => 'scalar', 'since' => 'PersistentParam1'],
	't2' => ['def' => null, 'type' => 'scalar', 'since' => 'PersistentParam2A'],
], TestPresenter::getReflection()->getPersistentParams());

Assert::same([
	'p1' => ['def' => 20, 'type' => 'integer', 'since' => 'BasePresenter'],
	'p3' => ['def' => null, 'type' => 'scalar', 'since' => 'SecondPresenter'],
	't1' => ['def' => null, 'type' => 'scalar', 'since' => 'PersistentParam1'],
	't3' => ['def' => null, 'type' => 'scalar', 'since' => 'PersistentParam3'],
], SecondPresenter::getReflection()->getPersistentParams());

Assert::same([
	'p1' => ['def' => null, 'type' => 'scalar', 'since' => 'BasePresenter'],
	't1' => ['def' => null, 'type' => 'scalar', 'since' => 'PersistentParam1'],
	't2' => ['def' => null, 'type' => 'scalar', 'since' => 'PersistentParam2A'],
], ThirdPresenter::getReflection()->getPersistentParams());

if (PHP_VERSION_ID >= 80000) {
	Assert::same([
		'p1' => ['def' => null, 'type' => 'scalar', 'since' => 'BasePresenter'],
		't1' => ['def' => null, 'type' => 'scalar', 'since' => 'PersistentParam1'],
	], FourthPresenter::getReflection()->getPersistentParams());
}

$url = new Http\UrlScript('http://localhost/index.php', '/index.php');

$presenterFactory = Mockery::mock(Nette\Application\IPresenterFactory::class);
$presenterFactory->shouldReceive('getPresenterClass')
	->andReturnUsing(fn($presenter) => $presenter . 'Presenter');

$presenter = new TestPresenter;
$presenter->injectPrimary(
	null,
	$presenterFactory,
	new Application\Routers\SimpleRouter,
	new Http\Request($url),
	new Http\Response,
);

$presenter->invalidLinkMode = TestPresenter::InvalidLinkWarning;
$presenter->autoCanonicalize = false;

$request = new Application\Request('Test', Http\Request::Get, []);
$presenter->run($request);
