<?php declare(strict_types=1);

/**
 * Test: class-level #[Requires] is inherited from ancestor classes
 */

use Nette\{Application, Http};
use Nette\Application\Attributes\Requires;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/functions.php';


#[Requires(forward: true)]
abstract class BaseRequiresPresenter extends Nette\Application\UI\Presenter
{
}

class InheritingRequiresPresenter extends BaseRequiresPresenter
{
	public function actionDefault(): never
	{
		$this->terminate();
	}
}

#[Requires(forward: false)]
class OverridingRequiresPresenter extends BaseRequiresPresenter
{
	public function actionDefault(): never
	{
		$this->terminate();
	}
}

#[Requires(methods: ['POST'])]
class CombiningRequiresPresenter extends BaseRequiresPresenter
{
	public function actionDefault(): never
	{
		$this->terminate();
	}
}


// rule inherited from ancestor is enforced
$presenter = createPresenter(InheritingRequiresPresenter::class);
Assert::noError(
	fn() => $presenter->run(new Application\Request('', Application\Request::FORWARD)),
);

Assert::exception(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get)),
	Application\BadRequestException::class,
	'Forwarded request is required by InheritingRequiresPresenter',
);


// rule explicitly set in descendant overrides the ancestor
$presenter = createPresenter(OverridingRequiresPresenter::class);
Assert::noError(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get)),
);


// descendant rule combines with a different inherited rule
$presenter = createPresenter(CombiningRequiresPresenter::class, method: 'POST');
Assert::exception(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get)),
	Application\BadRequestException::class,
	'Forwarded request is required by CombiningRequiresPresenter',
);

$presenter = createPresenter(CombiningRequiresPresenter::class, method: 'POST');
Assert::noError(
	fn() => $presenter->run(new Application\Request('', Application\Request::FORWARD)),
);

$presenter = createPresenter(CombiningRequiresPresenter::class);
Assert::exception(
	fn() => $presenter->run(new Application\Request('', Application\Request::FORWARD)),
	Application\BadRequestException::class,
	'Method GET is not allowed by CombiningRequiresPresenter',
);
