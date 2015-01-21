<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace NetteModule;

use Nette,
	Nette\Application,
	Tracy\ILogger;


/**
 * Default Error Presenter.
 *
 * @author     David Grudl
 */
class ErrorPresenter extends Nette\Object implements Application\IPresenter
{
	/** @var ILogger|NULL */
	private $logger;


	public function __construct(ILogger $logger = NULL)
	{
		$this->logger = $logger;
	}


	/**
	 * @return Application\IResponse
	 */
	public function run(Application\Request $request)
	{
		$e = $request->parameters['exception'];
		if ($e instanceof Application\BadRequestException) {
			$code = $e->getCode();
		} else {
			$code = 500;
			if ($this->logger) {
				$this->logger->log($e, ILogger::EXCEPTION);
			}
		}
		ob_start();
		require __DIR__ . '/templates/error.phtml';
		return new Application\Responses\TextResponse(ob_get_clean());
	}

}
