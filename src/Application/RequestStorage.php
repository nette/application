<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application;

use Nette,
	Nette\Http;


/**
 * Service for storing and restoring requests from session.
 *
 * @author     Martin Major
 */
class RequestStorage extends Nette\Object implements IRequestStorage
{
	/** @var Http\Session */
	private $session;

	/** @var Nette\Security\User */
	private $user;


	public function __construct(Http\Session $session, Nette\Security\User $user)
	{
		$this->session = $session;
		$this->user = $user;
	}


	/**
	 * Stores request and returns key.
	 * @return string key
	 */
	public function store(Request $request, Http\Url $url, $expiration = '10 minutes')
	{
		$session = $this->session->getSection(__CLASS__);
		do {
			$key = Nette\Utils\Random::generate(5);
		} while (isset($session[$key]));

		$session[$key] = [clone $request, clone $url, $this->user->getId()];
		$session->setExpiration($expiration, $key);
		return $key;
	}


	/**
	 * Restores original URL.
	 * @param  string key
	 * @return string|NULL
	 */
	public function getUrl($key)
	{
		list($request, $url, $user) = $this->session->getSection(__CLASS__)->$key;
		if (!$request || !$url || ($user !== NULL && $user !== $this->user->getId())) {
			return;
		}

		$request->setFlag($request::RESTORED, TRUE);
		$this->session->getFlashSection(__CLASS__)->request = $request;

		$url->setQueryParameter(Http\Session::FLASH_KEY, $this->session->getFlashId());
		return (string) $url;
	}


	/**
	 * Returns stored request.
	 * @return Request|NULL
	 */
	public function restore()
	{
		return $this->session->getFlashId()
			? $this->session->getFlashSection(__CLASS__)->request
			: NULL;
	}

}
