<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\UI;

use Nette;


/**
 * Web form adapted for Presenter.
 */
class Form extends Nette\Forms\Form implements ISignalReceiver
{

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


	/**
	 * @return void
	 */
	protected function validateParent(Nette\ComponentModel\IContainer $parent)
	{
		parent::validateParent($parent);
		$this->monitor(Presenter::class);
	}


	/**
	 * Returns the presenter where this component belongs to.
	 * @param  bool   throw exception if presenter doesn't exist?
	 * @return Presenter|NULL
	 */
	public function getPresenter($need = TRUE)
	{
		return $this->lookup(Presenter::class, $need);
	}


	/**
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 * @param  Nette\ComponentModel\IComponent
	 * @return void
	 */
	protected function attached($presenter)
	{
		if ($presenter instanceof Presenter) {
			$name = $this->lookupPath(Presenter::class);
			if (!isset($this->getElementPrototype()->id)) {
				$this->getElementPrototype()->id = 'frm-' . $name;
			}
			if (!$this->getAction()) {
				$this->setAction(new Link($presenter, $name . self::NAME_SEPARATOR . 'submit'));
			}

			$controls = $this->getControls();
			if (iterator_count($controls) && $this->isSubmitted()) {
				foreach ($controls as $control) {
					if (!$control->isDisabled()) {
						$control->loadHttpData();
					}
				}
			}
		}
		parent::attached($presenter);
	}


	/**
	 * Tells if the form is anchored.
	 * @return bool
	 */
	public function isAnchored()
	{
		return (bool) $this->getPresenter(FALSE);
	}


	/**
	 * Internal: returns submitted HTTP data or NULL when form was not submitted.
	 * @return array|NULL
	 */
	protected function receiveHttpData()
	{
		$presenter = $this->getPresenter();
		$isPost = $this->getMethod() === self::POST;

		$action = $this->getAction();
		if ($action instanceof Link) {
			$this[($isPost ? '_' : '') . Presenter::SIGNAL_KEY] =
				(new Nette\Forms\Controls\HiddenField($action->getDestination()))
				->setOmitted()->setHtmlId(FALSE);
			$this->setAction(new Link($presenter, 'this'));
		}

		if (!$presenter->isSignalReceiver($this, 'submit')) {
			return;
		}

		$request = $presenter->getRequest();
		if ($request->isMethod('forward') || $request->isMethod('post') !== $isPost) {
			return;
		}

		if ($isPost) {
			return Nette\Utils\Arrays::mergeTree($request->getPost(), $request->getFiles());
		} else {
			return $request->getParameters();
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
