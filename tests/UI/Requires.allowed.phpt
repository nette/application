<?php declare(strict_types=1);

/**
 * Test: #[Requires] option allowed
 */

use Nette\{Application, Http, Security};
use Nette\Application\Attributes\Requires;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


#[Requires(allowed: 'admin')]
abstract class AdminPresenter extends Nette\Application\UI\Presenter
{
}


class SettingsPresenter extends AdminPresenter
{
	public function actionDefault(): never
	{
		$this->terminate();
	}


	#[Requires(allowed: 'danger')]
	public function actionPurge(): never
	{
		$this->terminate();
	}
}


class SignInPresenter extends AdminPresenter
{
	public function denyAccess(): never
	{
		$this->redirectUrl('/sign-in');
	}


	public function actionDefault(): never
	{
		$this->terminate();
	}
}


/** A user allowed exactly the given resources. */
function createUser(array $allowed): Security\User
{
	$storage = new class implements Security\UserStorage {
		public function saveAuthentication(Security\IIdentity $identity): void
		{
		}


		public function clearAuthentication(bool $clearIdentity): void
		{
		}


		public function getState(): array
		{
			return [false, null, null];
		}


		public function setExpiration(?string $expire, bool $clearIdentity): void
		{
		}
	};
	$user = new Security\User($storage);
	if (method_exists($user, 'addVoter')) { // nette/security 4
		$user->addVoter(new class ($allowed) implements Security\Voter {
			public function __construct(
				private array $allowed,
			) {
			}


			public function vote(Security\Principal $principal, mixed $resource, string|UnitEnum|null $privilege): Security\Vote
			{
				return Security\Vote::allowIf(in_array($resource, $this->allowed, true));
			}
		});
	} else {
		$user->setAuthorizator(new class ($allowed) implements Security\Authorizator {
			public function __construct(
				private array $allowed,
			) {
			}


			public function isAllowed($role, $resource, $privilege): bool
			{
				return in_array($resource, $this->allowed, true);
			}
		});
	}

	return $user;
}


function runPresenter(string $class, array $allowed, string $action = 'default'): Application\Response
{
	$presenter = new $class;
	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript('http://localhost/')),
		new Http\Response,
		new Application\PresenterFactory,
		new Application\Routers\SimpleRouter,
		user: createUser($allowed),
	);
	$presenter->autoCanonicalize = false;
	return $presenter->run(new Application\Request('Test', Http\Request::Get, ['action' => $action]));
}


test('the class-level requirement is inherited', function () {
	Assert::noError(fn() => runPresenter(SettingsPresenter::class, ['admin']));
	Assert::exception(
		fn() => runPresenter(SettingsPresenter::class, []),
		Application\ForbiddenRequestException::class,
	);
});


test('an action adds its own requirement', function () {
	Assert::noError(fn() => runPresenter(SettingsPresenter::class, ['admin', 'danger'], 'purge'));
	Assert::exception(
		fn() => runPresenter(SettingsPresenter::class, ['admin'], 'purge'),
		Application\ForbiddenRequestException::class,
	);
});


test('the presenter decides what denial means', function () {
	$response = runPresenter(SignInPresenter::class, []);
	Assert::type(Application\Responses\RedirectResponse::class, $response);
	Assert::same('/sign-in', $response->getUrl());
});
