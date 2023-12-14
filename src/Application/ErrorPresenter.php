<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace NetteModule;

use Nette;
use Nette\Application;
use Nette\Http;
use Tracy\ILogger;


/**
 * Default Error Presenter.
 */
final class ErrorPresenter implements Application\IPresenter
{
	use Nette\SmartObject;

	public function __construct(
		private ?ILogger $logger = null,
	) {
	}


	public function run(Application\Request $request): Application\Response
	{
		$e = $request->getParameter('exception');
		if ($e instanceof Application\BadRequestException) {
			$code = $e->getHttpCode();
		} else {
			$code = 500;
			$this->logger?->log($e, ILogger::EXCEPTION);
		}

		return new Application\Responses\CallbackResponse(function (Http\IRequest $httpRequest, Http\IResponse $httpResponse) use ($code): void {
			if (preg_match('#^text/html(?:;|$)#', (string) $httpResponse->getHeader('Content-Type'))) {
				require __DIR__ . '/templates/error.phtml';
			}
		});
	}
}
