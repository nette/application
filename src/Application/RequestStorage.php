<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application;

use Nette;


/**
 * Service for storing and restoring requests from session.
 *
 * @author     Martin Major
 */
class RequestStorage extends Nette\Object implements IRequestStorage
{
	/** URL parameter key */
	const REQUEST_KEY = '_rid';

	/** @var Nette\Http\IRequest */
	private $httpRequest;

	/** @var Nette\Http\Session */
	private $session;

	/** @var Nette\Security\User */
	private $user;

	/** @var Nette\Application\IMessageStorage */
	private $flashStorage;



	public function __construct(Nette\Http\IRequest $httpRequest, Nette\Http\Session $session, Nette\Security\User $user, IMessageStorage $flashStorage)
	{
		$this->httpRequest = $httpRequest;
		$this->session = $session;
		$this->user = $user;
		$this->flashStorage = $flashStorage;
	}


	/**
	 * Stores current request to session.
	 * @param  Request application request
	 * @param  string expiration time
	 * @return string key
	 */
	public function storeRequest(Request $request, $expiration = '+ 10 minutes')
	{
		$session = $this->session->getSection('Nette.Application/requests');
		do {
			$key = Nette\Utils\Random::generate(5);
		} while (isset($session[$key]));

		$url = clone $this->httpRequest->getUrl();

		$session[$key] = array(
			'user' => $this->user->getId(),
			'url' => $url->appendQuery(array(static::REQUEST_KEY => $key))->getAbsoluteUrl(),
			'request' => $request,
		);
		$session->setExpiration($expiration, $key);
		return $key;
	}


	/**
	 * Restores request from session.
	 * @param  string key
	 * @return Responses\RedirectResponse|NULL
	 */
	public function restoreRequest($key)
	{
		list($request, $url) = $this->loadRequestFromSession($key);
		if ($request === NULL) {
			return NULL;
		}

		if ($this->flashStorage->hasMessages()) {
			$url .= '&' . IMessageStorage::FLASH_KEY . '=' . $this->flashStorage->getId();
		}

		return new Responses\RedirectResponse($url);
	}


	/**
	 * Returns stored request.
	 * @param  \Nette\Http\IRequest
	 * @return Request|NULL
	 */
	public function getRequest(Nette\Http\IRequest $httpRequest)
	{
		$key = $httpRequest->getQuery(static::REQUEST_KEY);

		list($request, $url) = $this->loadRequestFromSession($key);
		if ($request === NULL) {
			return NULL;
		}

		$flash = $this->httpRequest->getUrl()->getQueryParameter(IMessageStorage::FLASH_KEY);
		if ($flash !== NULL) {
			$parameters = $request->getParameters();
			$request->setParameters($parameters + array(IMessageStorage::FLASH_KEY => $flash));
		}

		return $request;
	}


	/**
	 * Loads request from session by its key.
	 * @param  string key
	 * @return array(Request, string)
	 */
	protected function loadRequestFromSession($key)
	{
		$session = $this->session->getSection('Nette.Application/requests');
		if (!isset($session[$key]) || ($session[$key]['user'] !== NULL && $session[$key]['user'] !== $this->user->getId())) {
			return array(NULL, NULL);
		}

		$request = clone $session[$key]['request'];
		$request->setFlag(Request::RESTORED, TRUE);

		return array($request, $session[$key]['url']);
	}

}
