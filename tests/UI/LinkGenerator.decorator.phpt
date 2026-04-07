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
	 * A simple decorator that wraps another LinkGeneratorInterface and tracks calls.
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


		public function requestToUrl(Request $request, ?bool $relative = false): string
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


	test('custom LinkGeneratorInterface can be injected into Presenter', function () {
		$inner = new Nette\Application\LinkGenerator(
			new Nette\Application\Routers\SimpleRouter,
			new Nette\Http\UrlScript('http://nette.org/en/'),
			new Nette\Application\PresenterFactory,
		);
		$decorator = new TrackingLinkGenerator($inner);

		$presenter = new App\Presentation\Homepage\HomepagePresenter;
		$presenter->injectPrimary(
			httpRequest: new Nette\Http\Request(new Nette\Http\UrlScript('http://nette.org/en/')),
			httpResponse: new Nette\Http\Response,
			linkGenerator: $decorator,
		);

		// Presenter should use the injected decorator
		$reflection = new ReflectionMethod($presenter, 'getLinkGenerator');
		$linkGenerator = $reflection->invoke($presenter);
		Assert::type(TrackingLinkGenerator::class, $linkGenerator);
		Assert::same($decorator, $linkGenerator);
	});


	test('decorator intercepts link generation', function () {
		$inner = new Nette\Application\LinkGenerator(
			new Nette\Application\Routers\SimpleRouter,
			new Nette\Http\UrlScript('http://nette.org/en/'),
			new Nette\Application\PresenterFactory,
		);
		$decorator = new TrackingLinkGenerator($inner);

		$presenter = new App\Presentation\Homepage\HomepagePresenter;
		$presenter->injectPrimary(
			httpRequest: new Nette\Http\Request(new Nette\Http\UrlScript('http://nette.org/en/')),
			httpResponse: new Nette\Http\Response,
			linkGenerator: $decorator,
		);

		// Trigger link generation through the decorator
		$reflection = new ReflectionMethod($presenter, 'getLinkGenerator');
		$linkGenerator = $reflection->invoke($presenter);

		$countBefore = $decorator->linkCallCount;
		$link = $linkGenerator->link('Homepage:default');

		Assert::same($countBefore + 1, $decorator->linkCallCount);
		Assert::same('http://nette.org/en/?action=default&presenter=Homepage', $link);
	});


	test('Presenter::getLinkGenerator() is overridable', function () {
		Assert::false((new ReflectionMethod(Nette\Application\UI\Presenter::class, 'getLinkGenerator'))->isFinal());
	});

}
