<?php declare(strict_types=1);

/**
 * Test: a non-coercible current-request parameter in a self-link degrades to a 4xx,
 * not an uncaught 500.
 */

use Nette\Application;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\InvalidRequestParameterException;
use Nette\Application\UI\Presenter;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class SelfLinkPresenter extends Presenter
{
	public string $op = '';
	public mixed $observed = null;


	protected function startup(): void
	{
		parent::startup();
		match ($this->op) {
			'redirect-this' => $this->redirect('this', ['keep' => 'x']),
			'redirect-explicit' => $this->redirect('default', ['tier' => 'abc']),
			'link-this' => $this->observed = $this->link('this'),
			'isLinkCurrent-this' => $this->observed = $this->isLinkCurrent('this'),
		};

		$this->terminate();
	}


	public function renderDefault(?int $tier = null): void
	{
	}
}


function runSelfLink(string $op, array $params): SelfLinkPresenter
{
	$factory = Mockery::mock(Application\IPresenterFactory::class);
	$factory->shouldReceive('getPresenterClass')->andReturnUsing(fn($name) => $name . 'Presenter');

	$presenter = new SelfLinkPresenter;
	$presenter->op = $op;
	$presenter->autoCanonicalize = false;
	$presenter->invalidLinkMode = Presenter::InvalidLinkTextual;
	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript('http://localhost/index.php', '/index.php')),
		new Http\Response,
		$factory,
		new Application\Routers\SimpleRouter,
	);
	$presenter->run(new Application\Request('Self', Http\Request::Get, $params + ['action' => 'default']));
	return $presenter;
}


// BC: subtype of InvalidLinkException
Assert::type(InvalidLinkException::class, new InvalidRequestParameterException);


// self-link with a bad current param -> 4xx
Assert::exception(
	fn() => runSelfLink('redirect-this', ['tier' => 'abc']),
	Application\BadRequestException::class,
	'Argument $tier passed to SelfLinkPresenter::renderDefault() must be ?int, string given.',
);

// a valid value still redirects normally
Assert::noError(fn() => runSelfLink('redirect-this', ['tier' => '5']));


// isLinkCurrent('this') -> false, no throw
Assert::false(runSelfLink('isLinkCurrent-this', ['tier' => 'abc'])->observed);


// link('this') stays graceful (#error), no leak
Assert::same(
	'#error: Argument $tier passed to SelfLinkPresenter::renderDefault() must be ?int, string given.',
	runSelfLink('link-this', ['tier' => 'abc'])->observed,
);


// explicit bad arg -> InvalidLinkException (programmer error)
Assert::exception(
	fn() => runSelfLink('redirect-explicit', []),
	InvalidLinkException::class,
	'Argument $tier passed to SelfLinkPresenter::renderDefault() must be ?int, string given.',
);
