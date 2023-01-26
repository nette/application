<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\UI;

use Nette;


/**
 * Web form adapted for Presenter.
 */
class Form extends Nette\Forms\Form implements SignalReceiver
{
	/** @var array<callable(self): void>  Occurs when form is attached to presenter */
	public array $onAnchor = [];


	/**
	 * Application form constructor.
	 */
	public function __construct(?Nette\ComponentModel\IContainer $parent = null, ?string $name = null)
	{
		parent::__construct();
		if ($parent !== null) {
			$parent->addComponent($this, $name);
		}
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
	 * Returns the presenter where this component belongs to.
	 */
	final public function getPresenter(): Presenter
	{
		return $this->lookup(Presenter::class, throw: true);
	}


	/**
	 * Returns the presenter where this component belongs to.
	 */
	final public function getPresenterIfExists(): ?Presenter
	{
		return $this->lookup(Presenter::class, throw: false);
	}


	public function hasPresenter(): bool
	{
		return (bool) $this->lookup(Presenter::class, throw: false);
	}


	/**
	 * Tells if the form is anchored.
	 */
	public function isAnchored(): bool
	{
		return $this->hasPresenter();
	}


	/** @deprecated  use allowCrossOrigin() */
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


	protected function beforeRender()
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
		if ($signal !== 'submit') {
			$class = static::class;
			throw new BadSignalException("Missing handler for signal '$signal' in $class.");

		} elseif (!$this->crossOrigin && !$this->getPresenter()->getHttpRequest()->isSameSite()) {
			$this->getPresenter()->detectedCsrf();

		} elseif (!$this->getPresenter()->getRequest()->hasFlag(Nette\Application\Request::RESTORED)) {
			$this->fireEvents();
		}
	}
}
