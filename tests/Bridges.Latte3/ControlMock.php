<?php

declare(strict_types=1);


class ControlMock extends Nette\Application\UI\Control
{
	public bool $snippetMode = true;

	public $payload = [];

	public $invalid = [];


	public function isControlInvalid(?string $name = null): bool
	{
		return $this->invalid === true || isset($this->invalid[$name]);
	}


	public function redrawControl(?string $name = null, bool $redraw = true): void
	{
		if ($this->invalid !== true) {
			unset($this->invalid[$name]);
		}
	}


	public function getSnippetId($name): string
	{
		return $name;
	}


	public function addSnippet($name, $content): void
	{
		$this->payload[$name] = $content;
	}


	public function getComponents(): array
	{
		return [];
	}


	public function getPresenter(): Nette\Application\UI\Presenter
	{
		$presenter = new class extends Nette\Application\UI\Presenter {
		};
		$this->payload = &$presenter->payload->snippets;
		return $presenter;
	}
}
