<?php

declare(strict_types=1);


function createPresenter(string $class, ...$options): Nette\Application\UI\Presenter
{
	$presenter = new $class;
	$presenter->injectPrimary(
		new Nette\Http\Request(new Nette\Http\UrlScript, ...$options),
		new Nette\Http\Response,
		null,
		new Nette\Application\Routers\SimpleRouter,
	);
	$presenter->autoCanonicalize = false;
	return $presenter;
}
