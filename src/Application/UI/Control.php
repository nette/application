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
 * @property-read ITemplate|Nette\Bridges\ApplicationLatte\Template|\stdClass $template
 */
abstract class Control extends Component implements IRenderable
{
	/** @var bool */
	public $snippetMode;

	/** @var ITemplateFactory */
	private $templateFactory;

	/** @var string|null */
	private $templateFile = null;

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


	final public function setTemplateFile(string $templateFile = null)
	{
		$this->templateFile = $templateFile;

		if ($this->template !== null) {
			$this->template->setFile($templateFile);
		}

		return $this;
	}


	final public function getTemplateFile(): ?string
	{
		return $this->templateFile;
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
		$template = $templateFactory->createTemplate($this);

		if ($this->templateFile !== null) {
			$template->setFile($this->templateFile);
		}

		return $template;
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
			$this->invalidSnippets[$snippet === null ? "\0" : $snippet] = true;

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

		} else {
			return $this->invalidSnippets[$snippet] ?? isset($this->invalidSnippets["\0"]);
		}
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
