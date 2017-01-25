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
 * @property-read ITemplate $template
 */
abstract class Control extends Component implements IRenderable
{
	/** @var ITemplateFactory */
	private $templateFactory;

	/** @var ITemplate */
	private $template;

	/** @var array */
	private $invalidSnippets = [];

	/** @var bool */
	public $snippetMode;


	/********************* template factory ****************d*g**/


	public function setTemplateFactory(ITemplateFactory $templateFactory)
	{
		$this->templateFactory = $templateFactory;
	}


	public function getTemplate(): ITemplate
	{
		if ($this->template === NULL) {
			$value = $this->createTemplate();
			if (!$value instanceof ITemplate && $value !== NULL) {
				$class2 = get_class($value); $class = get_class($this);
				throw new Nette\UnexpectedValueException("Object returned by $class::createTemplate() must be instance of Nette\\Application\\UI\\ITemplate, '$class2' given.");
			}
			$this->template = $value;
		}
		return $this->template;
	}


	protected function createTemplate(): ITemplate
	{
		$templateFactory = $this->templateFactory ?: $this->getPresenter()->getTemplateFactory();
		return $templateFactory->createTemplate($this);
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
	public function flashMessage(string $message, string $type = 'info'): \stdClass
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
	public function redrawControl($snippet = NULL, bool $redraw = TRUE): void
	{
		if ($redraw) {
			$this->invalidSnippets[$snippet === NULL ? "\0" : $snippet] = TRUE;

		} elseif ($snippet === NULL) {
			$this->invalidSnippets = [];

		} else {
			$this->invalidSnippets[$snippet] = FALSE;
		}
	}


	/**
	 * Is required to repaint the control or its snippet?
	 * @param  string  snippet name
	 */
	public function isControlInvalid(string $snippet = NULL): bool
	{
		if ($snippet === NULL) {
			if (count($this->invalidSnippets) > 0) {
				return TRUE;

			} else {
				$queue = [$this];
				do {
					foreach (array_shift($queue)->getComponents() as $component) {
						if ($component instanceof IRenderable) {
							if ($component->isControlInvalid()) {
								// $this->invalidSnippets['__child'] = TRUE; // as cache
								return TRUE;
							}

						} elseif ($component instanceof Nette\ComponentModel\IContainer) {
							$queue[] = $component;
						}
					}
				} while ($queue);

				return FALSE;
			}

		} else {
			return $this->invalidSnippets[$snippet] ?? isset($this->invalidSnippets["\0"]);
		}
	}


	/**
	 * Returns snippet HTML ID.
	 * @param  string  snippet name
	 */
	public function getSnippetId(string $name = NULL): string
	{
		// HTML 4 ID & NAME: [A-Za-z][A-Za-z0-9:_.-]*
		return 'snippet-' . $this->getUniqueId() . '-' . $name;
	}

}
