<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace NetteModule;

use Nette;
use Nette\Application;
use Tracy\ILogger;


/**
 * Default Error Presenter.
 */
class ErrorPresenter implements Application\IPresenter
{
	use Nette\SmartObject;

	/** @var ILogger|null */
	private $logger;


	public function __construct(ILogger $logger = null)
	{
		$this->logger = $logger;
	}


	public function run(Application\Request $request): Application\IResponse
	{
		$e = $request->getParameter('exception');
		if ($e instanceof Application\BadRequestException) {
			$code = $e->getHttpCode();
		} else {
			$code = 500;
			if ($this->logger) {
				$this->logger->log($e, ILogger::EXCEPTION);
			}
		}
		return new Application\Responses\CallbackResponse(function () use ($code) {
			require __DIR__ . '/templates/error.phtml';
		});
	}
}
