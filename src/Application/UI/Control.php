<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\UI;

use Nette;


/**
 * Control is renderable Presenter component.
 *
 * @property-read ITemplate|Nette\Bridges\ApplicationLatte\DefaultTemplate|\stdClass $template
 */
abstract class Control extends Component implements IRenderable
{
	/** @var bool */
	public $snippetMode;

	/** @var ITemplateFactory */
	private $templateFactory;

	/** @var ITemplate */
	private $template;

	/** @var array */
	private $invalidSnippets = [];


	/********************* template factory ****************d*g**/


	final public function setTemplateFactory(ITemplateFactory $templateFactory)
	{
		$this->templateFactory = $templateFactory;
		return $this;
	}


	final public function getTemplate(): ITemplate
	{
		if ($this->template === null) {
			$this->template = $this->createTemplate();
		}
		return $this->template;
	}


	protected function createTemplate(): ITemplate
	{
		$templateFactory = $this->templateFactory ?: $this->getPresenter()->getTemplateFactory();
		return $templateFactory->createTemplate($this, $this->formatTemplateClass());
	}


	public function formatTemplateClass(): ?string
	{
		$class = preg_replace('#Presenter$|Control$#', 'Template', static::class);
		if ($class === static::class || !class_exists($class)) {
			return null;
		} elseif (!is_a($class, ITemplate::class, true)) {
			trigger_error(sprintf(
				'%s: class %s was found but does not implement the %s, so it will not be used for the template.',
				static::class,
				$class,
				ITemplate::class
			), E_USER_NOTICE);
			return null;
		} else {
			return $class;
		}
	}


	/**
	 * Descendant can override this method to customize template compile-time filters.
	 */
	public function templatePrepareFilters(ITemplate $template): void
	{
	}


	/**
	 * Saves the message to template, that can be displayed after redirect.
	 */
	public function flashMessage($message, string $type = 'info'): \stdClass
	{
		$id = $this->getParameterId('flash');
		$messages = $this->getPresenter()->getFlashSession()->$id;
		$messages[] = $flash = (object) [
			'message' => $message,
			'type' => $type,
		];
		$this->getTemplate()->flashes = $messages;
		$this->getPresenter()->getFlashSession()->$id = $messages;
		return $flash;
	}


	/********************* rendering ****************d*g**/


	/**
	 * Forces control or its snippet to repaint.
	 */
	public function redrawControl(string $snippet = null, bool $redraw = true): void
	{
		if ($redraw) {
			$this->invalidSnippets[$snippet ?? "\0"] = true;

		} elseif ($snippet === null) {
			$this->invalidSnippets = [];

		} else {
			$this->invalidSnippets[$snippet] = false;
		}
	}


	/**
	 * Is required to repaint the control or its snippet?
	 */
	public function isControlInvalid(string $snippet = null): bool
	{
		if ($snippet !== null) {
			return $this->invalidSnippets[$snippet] ?? isset($this->invalidSnippets["\0"]);

		} elseif (count($this->invalidSnippets) > 0) {
			return true;
		}

		$queue = [$this];
		do {
			foreach (array_shift($queue)->getComponents() as $component) {
				if ($component instanceof IRenderable) {
					if ($component->isControlInvalid()) {
						// $this->invalidSnippets['__child'] = true; // as cache
						return true;
					}

				} elseif ($component instanceof Nette\ComponentModel\IContainer) {
					$queue[] = $component;
				}
			}
		} while ($queue);

		return false;
	}


	/**
	 * Returns snippet HTML ID.
	 */
	public function getSnippetId(string $name): string
	{
		// HTML 4 ID & NAME: [A-Za-z][A-Za-z0-9:_.-]*
		return 'snippet-' . $this->getUniqueId() . '-' . $name;
	}
}
