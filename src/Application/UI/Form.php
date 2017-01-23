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
	/** @var callable[]  function (self $sender); Occurs when form is attached to presenter */
	public $onAnchor;

	/**
	 * Application form constructor.
	 */
	public function __construct(Nette\ComponentModel\IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct();
		if ($parent !== NULL) {
			$parent->addComponent($this, $name);
		}
	}


	protected function validateParent(Nette\ComponentModel\IContainer $parent): void
	{
		parent::validateParent($parent);
		$this->monitor(Presenter::class);
	}


	/**
	 * Returns the presenter where this component belongs to.
	 * @param  bool   throw exception if presenter doesn't exist?
	 * @return Presenter|NULL
	 */
	public function getPresenter($throw = TRUE)
	{
		return $this->lookup(Presenter::class, $throw);
	}


	/**
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 */
	protected function attached(Nette\ComponentModel\IComponent $presenter): void
	{
		if ($presenter instanceof Presenter) {
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
		}
		parent::attached($presenter);
	}


	/**
	 * Tells if the form is anchored.
	 * @return bool
	 */
	public function isAnchored(): bool
	{
		return (bool) $this->getPresenter(FALSE);
	}


	/**
	 * Internal: returns submitted HTTP data or NULL when form was not submitted.
	 */
	protected function receiveHttpData(): ?array
	{
		$presenter = $this->getPresenter();
		if (!$presenter->isSignalReceiver($this, 'submit')) {
			return NULL;
		}

		$request = $presenter->getRequest();
		if ($request->isMethod('forward') || $request->isMethod('post') !== $this->isMethod('post')) {
			return NULL;
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
	 * @param  string
	 * @return void
	 */
	public function signalReceived($signal)
	{
		if ($signal === 'submit') {
			if (!$this->getPresenter()->getRequest()->hasFlag(Nette\Application\Request::RESTORED)) {
				$this->fireEvents();
			}
		} else {
			$class = get_class($this);
			throw new BadSignalException("Missing handler for signal '$signal' in $class.");
		}
	}

}
