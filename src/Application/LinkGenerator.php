<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application;

use Nette\Http\UrlScript;
use Nette\Routing\Router;
use Nette\Utils\Reflection;


/**
 * Link generator.
 */
final class LinkGenerator
{
	/** @internal */
	public ?Request $lastRequest = null;


	public function __construct(
		private readonly Router $router,
		private readonly UrlScript $refUrl,
		private readonly ?IPresenterFactory $presenterFactory = null,
	) {
	}


	/**
	 * Generates URL to presenter.
	 * @param  string  $destination  in format "[//] [[[module:]presenter:]action | signal! | this | @alias] [#fragment]"
	 * @throws UI\InvalidLinkException
	 */
	public function link(
		string $destination,
		array $args = [],
		?UI\Component $component = null,
		?string $mode = null,
	): ?string
	{
		$parts = self::parseDestination($destination);
		$args = $parts['args'] ?? $args;
		$request = $this->createRequest($component, $parts['path'] . ($parts['signal'] ? '!' : ''), $args, $mode ?? 'link');
		$relative = $mode === 'link' && !$parts['absolute'] && !$component?->getPresenter()->absoluteUrls;
		return $mode === 'forward' || $mode === 'test'
			? null
			: $this->requestToUrl($request, $relative) . $parts['fragment'];
	}


	/**
	 * @param  string  $destination  in format "[[[module:]presenter:]action | signal! | this | @alias]"
	 * @param  string  $mode  forward|redirect|link
	 * @throws UI\InvalidLinkException
	 * @internal
	 */
	public function createRequest(
		?UI\Component $component,
		string $destination,
		array $args,
		string $mode,
	): Request
	{
		// note: createRequest supposes that saveState(), run() & tryCall() behaviour is final

		$this->lastRequest = null;
		$refPresenter = $component?->getPresenter();
		$path = $destination;

		if (($component && !$component instanceof UI\Presenter) || str_ends_with($destination, '!')) {
			[$cname, $signal] = Helpers::splitName(rtrim($destination, '!'));
			if ($cname !== '') {
				$component = $component->getComponent(strtr($cname, ':', '-'));
			}

			if ($signal === '') {
				throw new UI\InvalidLinkException('Signal must be non-empty string.');
			}

			$path = 'this';
		}

		if ($path[0] === '@') {
			if (!$this->presenterFactory instanceof PresenterFactory) {
				throw new \LogicException('Link aliasing requires PresenterFactory service.');
			}
			$path = ':' . $this->presenterFactory->getAlias(substr($path, 1));
		}

		$current = false;
		[$presenter, $action] = Helpers::splitName($path);
		if ($presenter === '') {
			if (!$refPresenter) {
				throw new \LogicException("Presenter must be specified in '$destination'.");
			}
			$action = $path === 'this' ? $refPresenter->getAction() : $action;
			$presenter = $refPresenter->getName();
			$presenterClass = $refPresenter::class;

		} else {
			if ($presenter[0] === ':') { // absolute
				$presenter = substr($presenter, 1);
				if (!$presenter) {
					throw new UI\InvalidLinkException("Missing presenter name in '$destination'.");
				}
			} elseif ($refPresenter) { // relative
				[$module, , $sep] = Helpers::splitName($refPresenter->getName());
				$presenter = $module . $sep . $presenter;
			}

			try {
				$presenterClass = $this->presenterFactory?->getPresenterClass($presenter);
			} catch (InvalidPresenterException $e) {
				throw new UI\InvalidLinkException($e->getMessage(), 0, $e);
			}
		}

		// PROCESS SIGNAL ARGUMENTS
		if (isset($signal)) { // $component must be StatePersistent
			$reflection = new UI\ComponentReflection($component::class);
			if ($signal === 'this') { // means "no signal"
				$signal = '';
				if (array_key_exists(0, $args)) {
					throw new UI\InvalidLinkException("Unable to pass parameters to 'this!' signal.");
				}
			} elseif (!str_contains($signal, UI\Component::NameSeparator)) {
				// counterpart of signalReceived() & tryCall()

				$method = $reflection->getSignalMethod($signal);
				if (!$method) {
					throw new UI\InvalidLinkException("Unknown signal '$signal', missing handler {$reflection->getName()}::{$component::formatSignalMethod($signal)}()");
				} elseif ($this->isDeprecated($refPresenter, $method)) {
					trigger_error("Link to deprecated signal '$signal'" . ($component === $refPresenter ? '' : ' in ' . $component::class) . " from '{$refPresenter->getName()}:{$refPresenter->getAction()}'.", E_USER_DEPRECATED);
				}

				// convert indexed parameters to named
				UI\ParameterConverter::toParameters($method, $args, [], $missing);
			}

			// counterpart of StatePersistent
			if ($args && array_intersect_key($args, $reflection->getPersistentParams())) {
				$component->saveState($args);
			}

			if ($args && $component !== $refPresenter) {
				$prefix = $component->getUniqueId() . UI\Component::NameSeparator;
				foreach ($args as $key => $val) {
					unset($args[$key]);
					$args[$prefix . $key] = $val;
				}
			}
		}

		// PROCESS ARGUMENTS
		if (is_subclass_of($presenterClass, UI\Presenter::class)) {
			if ($action === '') {
				$action = UI\Presenter::DefaultAction;
			}

			$current = $refPresenter && ($action === '*' || strcasecmp($action, $refPresenter->getAction()) === 0) && $presenterClass === $refPresenter::class;

			$reflection = new UI\ComponentReflection($presenterClass);
			if ($this->isDeprecated($refPresenter, $reflection)) {
				trigger_error("Link to deprecated presenter '$presenter' from '{$refPresenter->getName()}:{$refPresenter->getAction()}'.", E_USER_DEPRECATED);
			}

			// counterpart of run() & tryCall()
			if ($method = $reflection->getActionRenderMethod($action)) {
				if ($this->isDeprecated($refPresenter, $method)) {
					trigger_error("Link to deprecated action '$presenter:$action' from '{$refPresenter->getName()}:{$refPresenter->getAction()}'.", E_USER_DEPRECATED);
				}

				UI\ParameterConverter::toParameters($method, $args, $path === 'this' ? $refPresenter->getParameters() : [], $missing);

			} elseif (array_key_exists(0, $args)) {
				throw new UI\InvalidLinkException("Unable to pass parameters to action '$presenter:$action', missing corresponding method $presenterClass::{$presenterClass::formatRenderMethod($action)}().");
			}

			// counterpart of StatePersistent
			if ($refPresenter) {
				if (empty($signal) && $args && array_intersect_key($args, $reflection->getPersistentParams())) {
					$refPresenter->saveStatePartial($args, $reflection);
				}

				$globalState = $refPresenter->getGlobalState($path === 'this' ? null : $presenterClass);
				if ($current && $args) {
					$tmp = $globalState + $refPresenter->getParameters();
					foreach ($args as $key => $val) {
						if (http_build_query([$val]) !== (isset($tmp[$key]) ? http_build_query([$tmp[$key]]) : '')) {
							$current = false;
							break;
						}
					}
				}

				$args += $globalState;
			}
		}

		if ($mode !== 'test' && !empty($missing)) {
			foreach ($missing as $rp) {
				if (!array_key_exists($rp->getName(), $args)) {
					throw new UI\InvalidLinkException("Missing parameter \${$rp->getName()} required by " . Reflection::toString($rp->getDeclaringFunction()));
				}
			}
		}

		// ADD ACTION & SIGNAL & FLASH
		if ($action) {
			$args[UI\Presenter::ActionKey] = $action;
		}

		if (!empty($signal)) {
			$args[UI\Presenter::SignalKey] = $component->getParameterId($signal);
			$current = $current && $args[UI\Presenter::SignalKey] === $refPresenter->getParameter(UI\Presenter::SignalKey);
		}

		if (($mode === 'redirect' || $mode === 'forward') && $refPresenter->hasFlashSession()) {
			$flashKey = $refPresenter->getParameter(UI\Presenter::FlashKey);
			$args[UI\Presenter::FlashKey] = is_string($flashKey) && $flashKey !== '' ? $flashKey : null;
		}

		return $this->lastRequest = new Request($presenter, Request::FORWARD, $args, flags: ['current' => $current]);
	}


	/**
	 * Parse destination in format "[//] [[[module:]presenter:]action | signal! | this | @alias] [?query] [#fragment]"
	 * @throws UI\InvalidLinkException
	 * @return array{absolute: bool, path: string, signal: bool, args: ?array, fragment: string}
	 * @internal
	 */
	public static function parseDestination(string $destination): array
	{
		if (!preg_match('~^ (?<absolute>//)?+ (?<path>[^!?#]++) (?<signal>!)?+ (?<query>\?[^#]*)?+ (?<fragment>\#.*)?+ $~x', $destination, $matches)) {
			throw new UI\InvalidLinkException("Invalid destination '$destination'.");
		}

		if (!empty($matches['query'])) {
			parse_str(substr($matches['query'], 1), $args);
		}

		return [
			'absolute' => (bool) $matches['absolute'],
			'path' => $matches['path'],
			'signal' => !empty($matches['signal']),
			'args' => $args ?? null,
			'fragment' => $matches['fragment'] ?? '',
		];
	}


	/**
	 * Converts Request to URL.
	 */
	public function requestToUrl(Request $request, ?bool $relative = false): string
	{
		$url = $this->router->constructUrl($request->toArray(), $this->refUrl);
		if ($url === null) {
			$params = $request->getParameters();
			unset($params[UI\Presenter::ActionKey], $params[UI\Presenter::PresenterKey]);
			$params = urldecode(http_build_query($params, '', ', '));
			throw new UI\InvalidLinkException("No route for {$request->getPresenterName()}:{$request->getParameter('action')}($params)");
		}

		if ($relative) {
			$hostUrl = $this->refUrl->getHostUrl() . '/';
			if (strncmp($url, $hostUrl, strlen($hostUrl)) === 0) {
				$url = substr($url, strlen($hostUrl) - 1);
			}
		}

		return $url;
	}


	public function withReferenceUrl(string $url): static
	{
		return new self(
			$this->router,
			new UrlScript($url),
			$this->presenterFactory,
		);
	}


	private function isDeprecated(?UI\Presenter $presenter, \ReflectionClass|\ReflectionMethod $reflection): bool
	{
		return $presenter?->invalidLinkMode
			&& (UI\ComponentReflection::parseAnnotation($reflection, 'deprecated') || $reflection->getAttributes(Attributes\Deprecated::class));
	}
}
