<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\UI;

use Nette;


/**
 * Component is the base class for all Presenter components.
 *
 * Components are persistent objects located on a presenter. They have ability to own
 * other child components, and interact with user. Components have properties
 * for storing their status, and responds to user command.
 *
 * @property-read Presenter $presenter
 * @property-read bool $linkCurrent
 */
abstract class Component extends Nette\ComponentModel\Container implements SignalReceiver, StatePersistent, \ArrayAccess
{
	use Nette\ComponentModel\ArrayAccess;

	/** @var array<callable(self): void>  Occurs when component is attached to presenter */
	public $onAnchor = [];

	/** @var array */
	protected $params = [];


	/**
	 * Returns the presenter where this component belongs to.
	 * @return Presenter
	 */
	public function getPresenter(): ?Presenter
	{
		if (func_num_args()) {
			trigger_error(__METHOD__ . '() parameter $throw is deprecated, use getPresenterIfExists()', E_USER_DEPRECATED);
			$throw = func_get_arg(0);
		}

		return $this->lookup(Presenter::class, throw: $throw ?? true);
	}


	/**
	 * Returns the presenter where this component belongs to.
	 */
	public function getPresenterIfExists(): ?Presenter
	{
		return $this->lookup(Presenter::class, throw: false);
	}


	/** @deprecated */
	public function hasPresenter(): bool
	{
		return (bool) $this->lookup(Presenter::class, throw: false);
	}


	/**
	 * Returns a fully-qualified name that uniquely identifies the component
	 * within the presenter hierarchy.
	 */
	public function getUniqueId(): string
	{
		return $this->lookupPath(Presenter::class);
	}


	protected function createComponent(string $name): ?Nette\ComponentModel\IComponent
	{
		$res = parent::createComponent($name);
		if ($res && !$res instanceof SignalReceiver && !$res instanceof StatePersistent) {
			$type = $res::class;
			trigger_error("It seems that component '$name' of type $type is not intended to be used in the Presenter.");
		}

		return $res;
	}


	protected function validateParent(Nette\ComponentModel\IContainer $parent): void
	{
		parent::validateParent($parent);
		$this->monitor(Presenter::class, function (Presenter $presenter): void {
			$this->loadState($presenter->popGlobalParameters($this->getUniqueId()));
			Nette\Utils\Arrays::invoke($this->onAnchor, $this);
		});
	}


	/**
	 * Calls public method if exists.
	 * @return bool  does method exist?
	 */
	protected function tryCall(string $method, array $params): bool
	{
		$rc = $this->getReflection();
		if (!$rc->hasMethod($method)) {
			return false;
		} elseif (!$rc->hasCallableMethod($method)) {
			throw new Nette\InvalidStateException('Method ' . Nette\Utils\Reflection::toString($rc->getMethod($method)) . ' is not callable.');
		}

		$rm = $rc->getMethod($method);
		$this->checkRequirements($rm);
		try {
			$args = $rc->combineArgs($rm, $params);
		} catch (Nette\InvalidArgumentException $e) {
			throw new Nette\Application\BadRequestException($e->getMessage());
		}

		$rm->invokeArgs($this, $args);
		return true;
	}


	/**
	 * Checks for requirements such as authorization.
	 */
	public function checkRequirements($element): void
	{
		if (
			$element instanceof \ReflectionMethod
			&& substr($element->getName(), 0, 6) === 'handle'
			&& !ComponentReflection::parseAnnotation($element, 'crossOrigin')
			&& (PHP_VERSION_ID < 80000 || !$element->getAttributes(Nette\Application\Attributes\CrossOrigin::class))
			&& !$this->getPresenter()->getHttpRequest()->isSameSite()
		) {
			$this->getPresenter()->detectedCsrf();
		}
	}


	/**
	 * Access to reflection.
	 */
	public static function getReflection(): ComponentReflection
	{
		return new ComponentReflection(static::class);
	}


	/********************* interface StatePersistent ****************d*g**/


	/**
	 * Loads state information.
	 */
	public function loadState(array $params): void
	{
		$reflection = $this->getReflection();
		foreach ($reflection->getParameters() as $name => $meta) {
			if (isset($params[$name])) { // nulls are ignored
				if (!$reflection->convertType($params[$name], $meta['type'])) {
					throw new Nette\Application\BadRequestException(sprintf(
						"Value passed to persistent parameter '%s' in %s must be %s, %s given.",
						$name,
						$this instanceof Presenter ? 'presenter ' . $this->getName() : "component '{$this->getUniqueId()}'",
						$meta['type'],
						is_object($params[$name]) ? get_class($params[$name]) : gettype($params[$name]),
					));
				}

				$this->$name = $params[$name];
			} else {
				$params[$name] = $this->$name ?? null;
			}
		}

		$this->params = $params;
	}


	/**
	 * Saves state information for next request.
	 */
	public function saveState(array &$params): void
	{
		$this->getReflection()->saveState($this, $params);
	}


	/**
	 * Returns component param.
	 * @return mixed
	 */
	final public function getParameter(string $name)
	{
		if (func_num_args() > 1) {
			$default = func_get_arg(1);
		}
		return $this->params[$name] ?? $default ?? null;
	}


	/**
	 * Returns component parameters.
	 */
	final public function getParameters(): array
	{
		return $this->params;
	}


	/**
	 * Returns a fully-qualified name that uniquely identifies the parameter.
	 */
	final public function getParameterId(string $name): string
	{
		$uid = $this->getUniqueId();
		return $uid === '' ? $name : $uid . self::NAME_SEPARATOR . $name;
	}


	/********************* interface SignalReceiver ****************d*g**/


	/**
	 * Calls signal handler method.
	 * @throws BadSignalException if there is not handler method
	 */
	public function signalReceived(string $signal): void
	{
		if (!$this->tryCall($this->formatSignalMethod($signal), $this->params)) {
			$class = static::class;
			throw new BadSignalException("There is no handler for signal '$signal' in class $class.");
		}
	}


	/**
	 * Formats signal handler method name -> case sensitivity doesn't matter.
	 */
	public static function formatSignalMethod(string $signal): string
	{
		return 'handle' . $signal;
	}


	/********************* navigation ****************d*g**/


	/**
	 * Generates URL to presenter, action or signal.
	 * @param  string   $destination in format "[//] [[[module:]presenter:]action | signal! | this] [#fragment]"
	 * @param  array|mixed  $args
	 * @throws InvalidLinkException
	 */
	public function link(string $destination, $args = []): string
	{
		try {
			$args = func_num_args() < 3 && is_array($args)
				? $args
				: array_slice(func_get_args(), 1);
			return $this->getPresenter()->createRequest($this, $destination, $args, 'link');

		} catch (InvalidLinkException $e) {
			return $this->getPresenter()->handleInvalidLink($e);
		}
	}


	/**
	 * Returns destination as Link object.
	 * @param  string   $destination in format "[//] [[[module:]presenter:]action | signal! | this] [#fragment]"
	 * @param  array|mixed  $args
	 */
	public function lazyLink(string $destination, $args = []): Link
	{
		$args = func_num_args() < 3 && is_array($args)
			? $args
			: array_slice(func_get_args(), 1);
		return new Link($this, $destination, $args);
	}


	/**
	 * Determines whether it links to the current page.
	 * @param  string   $destination in format "[//] [[[module:]presenter:]action | signal! | this] [#fragment]"
	 * @param  array|mixed  $args
	 * @throws InvalidLinkException
	 */
	public function isLinkCurrent(?string $destination = null, $args = []): bool
	{
		if ($destination !== null) {
			$args = func_num_args() < 3 && is_array($args)
				? $args
				: array_slice(func_get_args(), 1);
			$this->getPresenter()->createRequest($this, $destination, $args, 'test');
		}

		return $this->getPresenter()->getLastCreatedRequestFlag('current');
	}


	/**
	 * Redirect to another presenter, action or signal.
	 * @param  string   $destination in format "[//] [[[module:]presenter:]action | signal! | this] [#fragment]"
	 * @param  array|mixed  $args
	 * @return never
	 * @throws Nette\Application\AbortException
	 */
	public function redirect(string $destination, $args = []): void
	{
		$args = func_num_args() < 3 && is_array($args)
			? $args
			: array_slice(func_get_args(), 1);
		$presenter = $this->getPresenter();
		$presenter->redirectUrl($presenter->createRequest($this, $destination, $args, 'redirect'));
	}


	/**
	 * Permanently redirects to presenter, action or signal.
	 * @param  string   $destination in format "[//] [[[module:]presenter:]action | signal! | this] [#fragment]"
	 * @param  array|mixed  $args
	 * @return never
	 * @throws Nette\Application\AbortException
	 */
	public function redirectPermanent(string $destination, $args = []): void
	{
		$args = func_num_args() < 3 && is_array($args)
			? $args
			: array_slice(func_get_args(), 1);
		$presenter = $this->getPresenter();
		$presenter->redirectUrl(
			$presenter->createRequest($this, $destination, $args, 'redirect'),
			Nette\Http\IResponse::S301_MOVED_PERMANENTLY,
		);
	}


	/**
	 * Throws HTTP error.
	 * @throws Nette\Application\BadRequestException
	 */
	public function error(string $message = '', int $httpCode = Nette\Http\IResponse::S404_NOT_FOUND): void
	{
		throw new Nette\Application\BadRequestException($message, $httpCode);
	}
}


class_exists(PresenterComponent::class);
