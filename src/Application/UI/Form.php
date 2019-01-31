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
class Form extends Nette\Forms\Form implements ISignalReceiver
{
	/** @var callable[]  function (Form $sender): void; Occurs when form is attached to presenter */
	public $onAnchor;


	/**
	 * Application form constructor.
	 */
	public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null)
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

			$this->onAnchor($this);
		});
	}


	/**
	 * Returns the presenter where this component belongs to.
	 */
	final public function getPresenter(): ?Presenter
	{
		if (func_num_args()) {
			trigger_error(__METHOD__ . '() parameter $throw is deprecated, use hasPresenter()', E_USER_DEPRECATED);
			$throw = func_get_arg(0);
		}
		return $this->lookup(Presenter::class, $throw ?? true);
	}


	/**
	 * Returns whether there is a presenter.
	 */
	public function hasPresenter(): bool
	{
		return (bool) $this->lookup(Presenter::class, false);
	}


	/**
	 * Tells if the form is anchored.
	 */
	public function isAnchored(): bool
	{
		return $this->hasPresenter();
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

		if ($this->isMethod('post')) {
			return Nette\Utils\Arrays::mergeTree($request->getPost(), $request->getFiles());
		} else {
			return $request->getParameters();
		}
	}


	protected function beforeRender()
	{
		parent::beforeRender();
		$key = ($this->isMethod('post') ? '_' : '') . Presenter::SIGNAL_KEY;
		if (!isset($this[$key])) {
			$do = $this->lookupPath(Presenter::class) . self::NAME_SEPARATOR . 'submit';
			$this[$key] = (new Nette\Forms\Controls\HiddenField($do))->setOmitted();
		}
	}


	/********************* interface ISignalReceiver ****************d*g**/


	/**
	 * This method is called by presenter.
	 */
	public function signalReceived(string $signal): void
	{
		if ($signal === 'submit') {
			if (!$this->getPresenter()->getRequest()->hasFlag(Nette\Application\Request::RESTORED)) {
				$this->fireEvents();
			}
		} else {
			$class = get_class($this);
			throw new Nette\Application\RejectRequestException("Missing handler for signal '$signal' in $class.", Nette\Application\RejectRequestException::WRONG_SIGNAL);
		}
	}
}
