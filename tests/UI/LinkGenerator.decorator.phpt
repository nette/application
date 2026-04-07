<?php

/**
 * Test: Custom LinkGenerator decorator can be injected into Presenter.
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

	use Nette\Application\LinkGenerator;
	use Nette\Application\Request;
	use Nette\Application\UI\Component;
	use Tester\Assert;


	/**
	 * A decorator that wraps another LinkGenerator and tracks calls.
	 */
	class TrackingLinkGenerator implements LinkGenerator
	{
		public int $linkCallCount = 0;


		public function __construct(
			private readonly LinkGenerator $inner,
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
	class PrefixingLinkGenerator implements LinkGenerator
	{
		public function __construct(
			private readonly LinkGenerator $inner,
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
		$inner = new Nette\Application\DefaultLinkGenerator(
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
		$inner = new Nette\Application\DefaultLinkGenerator(
			new Nette\Application\Routers\SimpleRouter,
			new Nette\Http\UrlScript('http://nette.org/en/'),
		);
		$decorator = new PrefixingLinkGenerator($inner, '/proxy');

		$originalUrl = $inner->link('Homepage:default');
		$decoratedUrl = $decorator->link('Homepage:default');

		Assert::same('/proxy' . $originalUrl, $decoratedUrl);
	});


	test('decorator preserves null return for test mode', function () {
		$inner = new Nette\Application\DefaultLinkGenerator(
			new Nette\Application\Routers\SimpleRouter,
			new Nette\Http\UrlScript('http://nette.org/en/'),
		);
		$decorator = new PrefixingLinkGenerator($inner, '/proxy');

		Assert::null($decorator->link('Homepage:default', [], null, 'test'));
	});


	test('Presenter::getLinkGenerator() is not final', function () {
		Assert::false((new ReflectionMethod(Nette\Application\UI\Presenter::class, 'getLinkGenerator'))->isFinal());
	});

}
