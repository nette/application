<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application;


/**
 * Responsible for creating a new instance of given presenter.
 */
interface IPresenterFactory
{
	/**
	 * Generates and checks presenter class name.
	 * @return class-string<IPresenter>
	 * @throws InvalidPresenterException
	 */
	function getPresenterClass(string &$name): string;

	/**
	 * Creates new presenter instance.
	 */
	function createPresenter(string $name): IPresenter;
}
