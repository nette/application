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
class MessagesStorage extends Nette\Object implements IMessagesStorage
{
	/** @var Nette\Http\Session */
	private $session;

	/** @var string */
	private $id;



	/**
	 * @param  Nette\Http\Session
	 */
	public function __construct(Nette\Http\Session $session)
	{
		$this->session = $session;
	}


	/**
	 * @param  string
	 */
	public function setId($id)
	{
		$this->id = $id;
	}


	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * Checks if a flash session namespace exists.
	 * @return bool
	 */
	public function isOpened()
	{
		return $this->id && $this->session->hasSection('Nette.Application.Flash/' . $this->id);
	}


	/**
	 * Stores flash message for given component path and returns it's value object for further modifications.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return object
	 */
	public function addMessage($message, $type = 'info', $id = 'flash')
	{
		$messages = $this->getSession()->$id;
		$messages[] = $flash = (object) array(
			'message' => $message,
			'type' => $type,
		);
		$this->getSession()->$id = $messages;

		return $flash;
	}


	/**
	 * Returns array of stored flash messages for given component path.
	 * @param  string
	 * @return object[]
	 */
	public function getMessages($id = NULL)
	{
		return $this->getSession()->$id;
	}


	/**
	 * Returns session namespace provided to pass temporary data between redirects.
	 * @internal
	 * @return Nette\Http\SessionSection
	 */
	public function getSession()
	{
		if (!$this->id) {
			$this->id = Nette\Utils\Random::generate(4);
		}
		return $this->session->getSection('Nette.Application.Flash/' . $this->id);
	}



	/**
	 * Sets expiration for currently opened messages
	 * @param  string
	 * @return void
	 */
	public function setExpiration($expiration)
	{
		if ($this->isOpened()) {
			$this->getSession()->setExpiration($expiration);
		}
	}

}
