<?php

/**
 * Test: Custom LinkGeneratorInterface decorator can be injected into Presenter.
 */

declare(strict_types=1);

namespace {
	require __DIR__ . '/../bootstrap.php';
}

namespace App\Presentation\Homepage {

	use Nette;

	class HomepagePresenter extends Nette\Application\UI\Presenter
	{
		public function actionDefault()
		{
		}
	}

}

namespace {

	use Nette\Application\LinkGeneratorInterface;
	use Nette\Application\Request;
	use Nette\Application\UI\Component;
	use Tester\Assert;


	/**
	 * A decorator that wraps another LinkGeneratorInterface and tracks calls.
	 */
	class TrackingLinkGenerator implements LinkGeneratorInterface
	{
		public int $linkCallCount = 0;


		public function __construct(
			private readonly LinkGeneratorInterface $inner,
		) {
		}


		public function link(
			string $destination,
			array $args = [],
			?Component $component = null,
			?string $mode = null,
		): ?string
		{
			$this->linkCallCount++;

			return $this->inner->link($destination, $args, $component, $mode);
		}


		public function createRequest(
			?Component $component,
			string $destination,
			array $args,
			string $mode,
		): Request
		{
			return $this->inner->createRequest($component, $destination, $args, $mode);
		}


		public function requestToUrl(Request $request, bool $relative = false): string
		{
			return $this->inner->requestToUrl($request, $relative);
		}


		public function withReferenceUrl(string $url): static
		{
			return new static($this->inner->withReferenceUrl($url));
		}


		public function getLastRequest(): ?Request
		{
			return $this->inner->getLastRequest();
		}
	}


	/**
	 * A decorator that modifies generated URLs by adding a prefix.
	 */
	class PrefixingLinkGenerator implements LinkGeneratorInterface
	{
		public function __construct(
			private readonly LinkGeneratorInterface $inner,
			private readonly string $prefix,
		) {
		}


		public function link(
			string $destination,
			array $args = [],
			?Component $component = null,
			?string $mode = null,
		): ?string
		{
			$url = $this->inner->link($destination, $args, $component, $mode);

			return $url !== null ? $this->prefix . $url : null;
		}


		public function createRequest(
			?Component $component,
			string $destination,
			array $args,
			string $mode,
		): Request
		{
			return $this->inner->createRequest($component, $destination, $args, $mode);
		}


		public function requestToUrl(Request $request, bool $relative = false): string
		{
			return $this->inner->requestToUrl($request, $relative);
		}


		public function withReferenceUrl(string $url): static
		{
			return new static($this->inner->withReferenceUrl($url), $this->prefix);
		}


		public function getLastRequest(): ?Request
		{
			return $this->inner->getLastRequest();
		}
	}


	test('injected decorator is used by Presenter', function () {
		$inner = new Nette\Application\LinkGenerator(
			new Nette\Application\Routers\SimpleRouter,
			new Nette\Http\UrlScript('http://nette.org/en/'),
		);
		$decorator = new TrackingLinkGenerator($inner);

		$presenter = new App\Presentation\Homepage\HomepagePresenter;
		$presenter->injectPrimary(
			httpRequest: new Nette\Http\Request(new Nette\Http\UrlScript('http://nette.org/en/')),
			httpResponse: new Nette\Http\Response,
			linkGenerator: $decorator,
		);

		Assert::same(0, $decorator->linkCallCount);
		$presenter->link(':Homepage:default');
		Assert::same(1, $decorator->linkCallCount);
	});


	test('decorator can modify generated URLs', function () {
		$inner = new Nette\Application\LinkGenerator(
			new Nette\Application\Routers\SimpleRouter,
			new Nette\Http\UrlScript('http://nette.org/en/'),
		);
		$decorator = new PrefixingLinkGenerator($inner, '/proxy');

		$originalUrl = $inner->link('Homepage:default');
		$decoratedUrl = $decorator->link('Homepage:default');

		Assert::same('/proxy' . $originalUrl, $decoratedUrl);
	});


	test('decorator preserves null return for test mode', function () {
		$inner = new Nette\Application\LinkGenerator(
			new Nette\Application\Routers\SimpleRouter,
			new Nette\Http\UrlScript('http://nette.org/en/'),
		);
		$decorator = new PrefixingLinkGenerator($inner, '/proxy');

		Assert::null($decorator->link('Homepage:default', [], null, 'test'));
	});


	test('Presenter::getLinkGenerator() is not final', function () {
		Assert::false((new ReflectionMethod(Nette\Application\UI\Presenter::class, 'getLinkGenerator'))->isFinal());
	});


	test('LinkGeneratorInterface is resolvable from DI container', function () {
		$compiler = new Nette\DI\Compiler;
		$compiler->addExtension('application', new Nette\Bridges\ApplicationDI\ApplicationExtension(false));

		$builder = $compiler->getContainerBuilder();
		$builder->addDefinition('myRouter')->setFactory(Nette\Application\Routers\SimpleRouter::class);
		$builder->addDefinition('myHttpRequest')->setFactory(Nette\Http\Request::class, [new Nette\DI\Definitions\Statement(Nette\Http\UrlScript::class)]);
		$builder->addDefinition('myHttpResponse')->setFactory(Nette\Http\Response::class);

		$code = $compiler->setClassName('DecoratorTestContainer')->compile();
		eval($code);

		$container = new DecoratorTestContainer;
		$service = $container->getByType(Nette\Application\LinkGeneratorInterface::class);
		Assert::type(Nette\Application\LinkGeneratorInterface::class, $service);
		Assert::type(Nette\Application\LinkGenerator::class, $service);
	});

}
