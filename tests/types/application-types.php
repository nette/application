<?php declare(strict_types=1);

/**
 * PHPStan type tests for Application.
 */

use Nette\Application;
use Nette\Application\Helpers;
use Nette\Application\LinkGenerator;
use Nette\Application\Routers\RouteList;
use Nette\Application\UI\Component;
use Nette\Application\UI\ComponentReflection;
use Nette\Application\UI\Control;
use Nette\Application\UI\Multiplier;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Template;
use Nette\Bridges\ApplicationLatte\DefaultTemplate;
use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Nette\ComponentModel\IComponent;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\Routing\Router;
use function PHPStan\Testing\assertType;


function testRouteListArrayAccess(RouteList $routeList): void
{
	$router = $routeList[0];
	assertType(Router::class, $router);
}


function testComponentArrayAccess(Component $component): void
{
	$child = $component['name'];
	assertType(IComponent::class, $child);
}


function testPresenterGetSession(Presenter $presenter): void
{
	$session = $presenter->getSession();
	assertType(Session::class, $session);

	$section = $presenter->getSession('section');
	assertType(SessionSection::class, $section);
}


function testPresenterGetPresenter(Component $component): void
{
	$presenter = $component->getPresenter();
	assertType(Presenter::class, $presenter);

	$presenter = $component->getPresenter(throw: true);
	assertType(Presenter::class, $presenter);

	$presenterOrNull = $component->getPresenter(throw: false);
	assertType(Presenter::class . '|null', $presenterOrNull);
}


/**
 * @param Multiplier<Component> $multiplier
 */
function testMultiplierGeneric(Multiplier $multiplier): void
{
	assertType('Nette\Application\UI\Multiplier<Nette\Application\UI\Component>', $multiplier);
}


function testPresenterGetSignal(Presenter $presenter): void
{
	$signal = $presenter->getSignal();
	assertType('array{string, string}|null', $signal);
}


function testControlCreateTemplate(): void
{
	$control = new class extends Control {
		/**
		 * @template T of Template
		 * @param ?class-string<T>  $class
		 * @return ($class is null ? Template : T)
		 */
		public function exposedCreateTemplate(?string $class = null): Template
		{
			return $this->createTemplate($class);
		}
	};
	$template = $control->exposedCreateTemplate();
	assertType(Template::class, $template);

	$template = $control->exposedCreateTemplate(DefaultTemplate::class);
	assertType(DefaultTemplate::class, $template);
}


function testPresenterCreateTemplate(): void
{
	$presenter = new class extends Presenter {
		/**
		 * @template T of Template
		 * @param ?class-string<T>  $class
		 * @return ($class is null ? Template : T)
		 */
		public function exposedCreateTemplate(?string $class = null): Template
		{
			return $this->createTemplate($class);
		}
	};
	$template = $presenter->exposedCreateTemplate();
	assertType(Template::class, $template);

	$template = $presenter->exposedCreateTemplate(DefaultTemplate::class);
	assertType(DefaultTemplate::class, $template);
}


function testPresenterFormatTemplateFiles(Presenter $presenter): void
{
	$files = $presenter->formatTemplateFiles();
	assertType('non-empty-list<string>', $files);

	$files = $presenter->formatLayoutTemplateFiles();
	assertType('non-empty-list<string>', $files);
}


function testRequestTypes(Application\Request $request): void
{
	assertType('array<string, mixed>', $request->getParameters());
	assertType('mixed', $request->getParameter('key'));
	assertType('array<string, mixed>', $request->getFiles());
	assertType('array<string, mixed>', $request->toArray());
}


function testHelpersSplitName(): void
{
	assertType('array{string, string, string}', Helpers::splitName('Module:Presenter'));
}


function testHelpersGetClassesAndTraits(): void
{
	assertType('array<string, class-string>', Helpers::getClassesAndTraits(Presenter::class));
}


function testParseDestination(): void
{
	assertType(
		'array{absolute: bool, path: string, signal: bool, args: array<string, mixed>|null, fragment: string}',
		LinkGenerator::parseDestination('Presenter:action'),
	);
}


function testTemplateFactoryCreateTemplate(TemplateFactory $factory): void
{
	$template = $factory->createTemplate();
	assertType('Nette\Bridges\ApplicationLatte\Template', $template);
}


function testApplicationTypes(Application\Application $app): void
{
	assertType('list<Nette\Application\Request>', $app->getRequests());
	assertType('Nette\Application\IPresenter|null', $app->getPresenter());
}


function testComponentReflectionTypes(ComponentReflection $ref): void
{
	assertType('array<string, array{def: mixed, type: string, since?: class-string|null}>', $ref->getParameters());
	assertType('array<string, array{def: mixed, type: string, since: class-string|null}>', $ref->getPersistentParams());
	assertType('array<string, array{since: class-string}>', $ref->getPersistentComponents());
}
