<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application;

use Nette;


/**
 * @author Filip Procházka
 */
interface IMessageStorage
{

	const FLASH_KEY = '_fid';

	/**
	 * @param  string
	 */
	function setId($id);


	/**
	 * @return string
	 */
	function getId();


	/**
	 * Checks if a storage contains messages.
	 * @return bool
	 */
	function hasMessages();


	/**
	 * Stores flash message for given component path and returns it's value object for further modifications.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return object
	 */
	function addMessage($message, $type = 'info', $id = 'flash');


	/**
	 * Returns array of stored flash messages for given component path.
	 * @param  string
	 * @return object[]
	 */
	function getMessages($id = NULL);


	/**
	 * Sets expiration for currently opened messages
	 * @param  string
	 * @return void
	 */
	function setExpiration($expiration);

}
