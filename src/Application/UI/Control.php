<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\UI;

use Nette;


/**
 * Control is renderable Presenter component.
 *
 * @property-read ITemplate|Nette\Bridges\ApplicationLatte\Template|\stdClass $template
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


	public function setTemplateFactory(ITemplateFactory $templateFactory)
	{
		$this->templateFactory = $templateFactory;
	}


	/**
	 * @return ITemplate
	 */
	public function getTemplate()
	{
		if ($this->template === null) {
			$value = $this->createTemplate();
			if (!$value instanceof ITemplate && $value !== null) {
				$class2 = get_class($value);
				$class = get_class($this);
				throw new Nette\UnexpectedValueException("Object returned by $class::createTemplate() must be instance of Nette\\Application\\UI\\ITemplate, '$class2' given.");
			}
			$this->template = $value;
		}
		return $this->template;
	}


	/**
	 * @return ITemplate
	 */
	protected function createTemplate()
	{
		$templateFactory = $this->templateFactory ?: $this->getPresenter()->getTemplateFactory();
		return $templateFactory->createTemplate($this);
	}


	/**
	 * Descendant can override this method to customize template compile-time filters.
	 * @param  ITemplate
	 * @return void
	 */
	public function templatePrepareFilters($template)
	{
	}


	/**
	 * Saves the message to template, that can be displayed after redirect.
	 * @param  string
	 * @param  string
	 * @return \stdClass
	 */
	public function flashMessage($message, $type = 'info')
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
	 * @return void
	 */
	public function redrawControl($snippet = null, $redraw = true)
	{
		if ($redraw) {
			$this->invalidSnippets[$snippet === null ? "\0" : $snippet] = true;

		} elseif ($snippet === null) {
			$this->invalidSnippets = [];

		} else {
			$this->invalidSnippets[$snippet] = false;
		}
	}


	/** @deprecated */
	public function invalidateControl($snippet = null)
	{
		trigger_error(__METHOD__ . '() is deprecated; use $this->redrawControl($snippet) instead.', E_USER_DEPRECATED);
		$this->redrawControl($snippet);
	}


	/** @deprecated */
	public function validateControl($snippet = null)
	{
		trigger_error(__METHOD__ . '() is deprecated; use $this->redrawControl($snippet, false) instead.', E_USER_DEPRECATED);
		$this->redrawControl($snippet, false);
	}


	/**
	 * Is required to repaint the control or its snippet?
	 * @param  string  snippet name
	 * @return bool
	 */
	public function isControlInvalid($snippet = null)
	{
		if ($snippet === null) {
			if (count($this->invalidSnippets) > 0) {
				return true;

			} else {
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

		} elseif (isset($this->invalidSnippets[$snippet])) {
			return $this->invalidSnippets[$snippet];
		} else {
			return isset($this->invalidSnippets["\0"]);
		}
	}


	/**
	 * Returns snippet HTML ID.
	 * @param  string  snippet name
	 * @return string
	 */
	public function getSnippetId($name = null)
	{
		// HTML 4 ID & NAME: [A-Za-z][A-Za-z0-9:_.-]*
		return 'snippet-' . $this->getUniqueId() . '-' . $name;
	}
}
