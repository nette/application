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
	private ?Nette\Http\FetchSite $allowedOrigin = Nette\Http\FetchSite::SameOrigin;


	public function __construct(?Nette\ComponentModel\IContainer $parent = null, ?string $name = null)
	{
		parent::__construct();
		$parent?->addComponent($this, $name);
	}


	/**
	 * The form is anchored via a presenter; the source is set when the form is attached to it.
	 */
	protected function createDefaultSource(): ?Nette\Forms\SubmissionSource
	{
		return null;
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

			if (!$this->isAnchored()) { // the write-once source survives re-anchoring, it resolves the current presenter itself
				$this->setSubmissionSource(new FormSubmissionSource); // lets already attached controls load their values
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
	 * Disables the same-origin (Sec-Fetch) CSRF check, allowing cross-origin form submissions.
	 */
	public function allowCrossOrigin(): void
	{
		$this->allowedOrigin = null;
	}


	#[\Deprecated('use allowCrossOrigin()')]
	public function disableSameSiteProtection(): void
	{
		$this->allowCrossOrigin();
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

		} elseif ($this->allowedOrigin && !$presenter->getHttpRequest()->isFrom($this->allowedOrigin)) {
			$presenter->detectedCsrf();

		} elseif (!$presenter->getRequest()->hasFlag(Nette\Application\Request::RESTORED)) {
			$this->fireEvents();
		}
	}
}
