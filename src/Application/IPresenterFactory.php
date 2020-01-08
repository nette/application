<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application;


/**
 * Responsible for creating a new instance of given presenter.
 */
interface IPresenterFactory
{
	/**
	 * Generates and checks presenter class name.
	 * @throws InvalidPresenterException
	 */
	function getPresenterClass(string &$name): string;

	/**
	 * Creates new presenter instance.
	 */
	function createPresenter(string $name): IPresenter;
}
