<?php declare(strict_types=1);

/**
 * Test: Nette\Application\UI\Presenter::link() and backed enum parameters
 */

use Nette\Application;
use Nette\Application\Attributes\Persistent;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


enum Sort: string
{
	case Asc = 'asc';
	case Desc = 'desc';
}


class TestPresenter extends Application\UI\Presenter
{
	#[Persistent]
	public Sort $sort = Sort::Asc;


	public function actionDefault(): void
	{
		// value from URL was converted to enum
		Assert::same(Sort::Desc, $this->sort);

		// non-default value is carried in URL as the case value
		Assert::same('/index.php?sort=desc&action=default&presenter=Test', $this->link('this'));

		// enum instance and its value are accepted in args
		Assert::same('/index.php?sort=desc&action=default&presenter=Test', $this->link('this', ['sort' => Sort::Desc]));
		Assert::same('/index.php?sort=desc&action=default&presenter=Test', $this->link('this', ['sort' => 'desc']));

		// default value is omitted
		Assert::same('/index.php?action=default&presenter=Test', $this->link('this', ['sort' => Sort::Asc]));
		$this->sort = Sort::Asc;
		Assert::same('/index.php?action=default&presenter=Test', $this->link('this'));

		// enum in action method argument
		Assert::same('/index.php?dir=desc&action=other&presenter=Test', $this->link('other', ['dir' => Sort::Desc]));
		Assert::same('/index.php?dir=desc&action=other&presenter=Test', $this->link('other', ['dir' => 'desc']));
		Assert::same('/index.php?action=other&presenter=Test', $this->link('other', ['dir' => Sort::Asc]));

		$this->terminate();
	}


	public function actionOther(Sort $dir = Sort::Asc): void
	{
	}
}


function createPresenter(): TestPresenter
{
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
	$presenter->autoCanonicalize = false;
	return $presenter;
}


createPresenter()->run(new Application\Request('Test', Http\Request::Get, ['sort' => 'desc']));


Assert::exception(
	fn() => createPresenter()->run(new Application\Request('Test', Http\Request::Get, ['sort' => 'bogus'])),
	Application\BadRequestException::class,
	"Value passed to persistent parameter 'sort' in presenter Test must be Sort, string given.",
);


Assert::exception(
	fn() => createPresenter()->run(new Application\Request('Test', Http\Request::Get, ['sort' => ['name' => 'desc', 'value' => 'desc']])),
	Application\BadRequestException::class,
	"Value passed to persistent parameter 'sort' in presenter Test must be Sort, array given.",
);


Assert::exception(
	fn() => createPresenter()->run(new Application\Request('Test', Http\Request::Get, ['action' => 'other', 'dir' => 'bogus'])),
	Application\BadRequestException::class,
	'Argument $dir passed to TestPresenter::actionOther() must be Sort, string given.',
);
