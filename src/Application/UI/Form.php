<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\UI;

use Nette;


/**
 * Web form adapted for Presenter.
 */
class Form extends Nette\Forms\Form implements SignalReceiver
{
	/** @var array<callable(static): void>  Occurs when form is attached to presenter */
	public array $onAnchor = [];


	public function __construct(?Nette\ComponentModel\IContainer $parent = null, ?string $name = null)
	{
		parent::__construct();
		$parent?->addComponent($this, $name);
	}


	protected function validateParent(Nette\ComponentModel\IContainer $parent): void
	{
		parent::validateParent($parent);

		$this->monitor(Presenter::class, function (Presenter $presenter): void {
			if (!isset($this->getElementPrototype()->id)) {
				$this->getElementPrototype()->id = 'frm-' . $this->lookupPath(Presenter::class);
			}

			if (!$this->getAction()) {
				$this->setAction(new Link($presenter, 'this'));
			}

			$controls = $this->getControls();
			if (iterator_count($controls) && $this->isSubmitted()) {
				foreach ($controls as $control) {
					if (!$control->isDisabled()) {
						$control->loadHttpData();
					}
				}
			}

			Nette\Utils\Arrays::invoke($this->onAnchor, $this);
		});
	}


	/**
	 * Returns the presenter where this component belongs to. Throws if not attached.
	 * @return ($throw is true ? Presenter : ?Presenter)
	 */
	final public function getPresenter(bool $throw = true): ?Presenter
	{
		return $this->lookup(Presenter::class, $throw);
	}


	/**
	 * Returns the presenter where this component belongs to, or null if not attached.
	 * @deprecated
	 */
	final public function getPresenterIfExists(): ?Presenter
	{
		return $this->lookup(Presenter::class, throw: false);
	}


	/** @deprecated */
	public function hasPresenter(): bool
	{
		return (bool) $this->lookup(Presenter::class, throw: false);
	}


	/**
	 * Tells if the form is anchored.
	 */
	public function isAnchored(): bool
	{
		return (bool) $this->getPresenter(throw: false);
	}


	#[\Deprecated('use allowCrossOrigin()')]
	public function disableSameSiteProtection(): void
	{
		$this->allowCrossOrigin();
	}


	/**
	 * Internal: returns submitted HTTP data or null when form was not submitted.
	 */
	protected function receiveHttpData(): ?array
	{
		$presenter = $this->getPresenter();
		if (!$presenter->isSignalReceiver($this, 'submit')) {
			return null;
		}

		$request = $presenter->getRequest();
		if ($request->isMethod('forward') || $request->isMethod('post') !== $this->isMethod('post')) {
			return null;
		}

		return $this->isMethod('post')
			? Nette\Utils\Arrays::mergeTree($request->getPost(), $request->getFiles())
			: $request->getParameters();
	}


	protected function beforeRender(): void
	{
		parent::beforeRender();
		$key = ($this->isMethod('post') ? '_' : '') . Presenter::SignalKey;
		if (!isset($this[$key]) && $this->getAction() !== '') {
			$do = $this->lookupPath(Presenter::class) . self::NameSeparator . 'submit';
			$this[$key] = (new Nette\Forms\Controls\HiddenField($do))->setOmitted();
		}
	}


	/********************* interface SignalReceiver ****************d*g**/


	/**
	 * This method is called by presenter.
	 */
	public function signalReceived(string $signal): void
	{
		$presenter = $this->getPresenter();
		if ($signal !== 'submit') {
			$class = static::class;
			throw new BadSignalException("Missing handler for signal '$signal' in $class.");

		} elseif (!$this->crossOrigin && !$presenter->getHttpRequest()->isSameSite()) {
			$presenter->detectedCsrf();

		} elseif (!$presenter->getRequest()->hasFlag(Nette\Application\Request::RESTORED)) {
			$this->fireEvents();
		}
	}
}
