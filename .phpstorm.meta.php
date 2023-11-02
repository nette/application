<?php

declare(strict_types=1);

namespace PHPSTORM_META;

expectedArguments(\Nette\Application\Routers\Route::__construct(), 2, \Nette\Routing\Router::ONE_WAY);
expectedArguments(\Nette\Application\Routers\SimpleRouter::__construct(), 1, \Nette\Routing\Router::ONE_WAY);

registerArgumentsSet('nette_http_codes_3xx',
	\Nette\Http\IResponse::S300_MULTIPLE_CHOICES,
	\Nette\Http\IResponse::S301_MOVED_PERMANENTLY,
	\Nette\Http\IResponse::S302_FOUND,
	\Nette\Http\IResponse::S303_SEE_OTHER,
	\Nette\Http\IResponse::S303_POST_GET,
	\Nette\Http\IResponse::S304_NOT_MODIFIED,
	\Nette\Http\IResponse::S305_USE_PROXY,
	\Nette\Http\IResponse::S307_TEMPORARY_REDIRECT,
	\Nette\Http\IResponse::S308_PERMANENT_REDIRECT,
);
registerArgumentsSet('nette_http_codes_4xx',
	\Nette\Http\IResponse::S400_BAD_REQUEST,
	\Nette\Http\IResponse::S401_UNAUTHORIZED,
	\Nette\Http\IResponse::S402_PAYMENT_REQUIRED,
	\Nette\Http\IResponse::S403_FORBIDDEN,
	\Nette\Http\IResponse::S404_NOT_FOUND,
	\Nette\Http\IResponse::S405_METHOD_NOT_ALLOWED,
	\Nette\Http\IResponse::S406_NOT_ACCEPTABLE,
	\Nette\Http\IResponse::S407_PROXY_AUTHENTICATION_REQUIRED,
	\Nette\Http\IResponse::S408_REQUEST_TIMEOUT,
	\Nette\Http\IResponse::S409_CONFLICT,
	\Nette\Http\IResponse::S410_GONE,
	\Nette\Http\IResponse::S411_LENGTH_REQUIRED,
	\Nette\Http\IResponse::S412_PRECONDITION_FAILED,
	\Nette\Http\IResponse::S413_REQUEST_ENTITY_TOO_LARGE,
	\Nette\Http\IResponse::S414_REQUEST_URI_TOO_LONG,
	\Nette\Http\IResponse::S415_UNSUPPORTED_MEDIA_TYPE,
	\Nette\Http\IResponse::S416_REQUESTED_RANGE_NOT_SATISFIABLE,
	\Nette\Http\IResponse::S417_EXPECTATION_FAILED,
	\Nette\Http\IResponse::S421_MISDIRECTED_REQUEST,
	\Nette\Http\IResponse::S422_UNPROCESSABLE_ENTITY,
	\Nette\Http\IResponse::S423_LOCKED,
	\Nette\Http\IResponse::S424_FAILED_DEPENDENCY,
	\Nette\Http\IResponse::S426_UPGRADE_REQUIRED,
	\Nette\Http\IResponse::S428_PRECONDITION_REQUIRED,
	\Nette\Http\IResponse::S429_TOO_MANY_REQUESTS,
	\Nette\Http\IResponse::S431_REQUEST_HEADER_FIELDS_TOO_LARGE,
	\Nette\Http\IResponse::S451_UNAVAILABLE_FOR_LEGAL_REASONS,
);

expectedArguments(\Nette\Application\UI\Presenter::redirectUrl(), 1, argumentsSet('nette_http_codes_3xx'));
expectedArguments(\Nette\Application\Responses\RedirectResponse::__construct(), 1, argumentsSet('nette_http_codes_3xx'));
expectedArguments(\Nette\Application\UI\Component::error(), 1, argumentsSet('nette_http_codes_4xx'));

expectedArguments(\Nette\Application\BadRequestException::__construct(), 1, argumentsSet('nette_http_codes_4xx'));
expectedReturnValues(\Nette\Application\BadRequestException::getHttpCode(), argumentsSet('nette_http_codes_4xx'));

exitPoint(\Nette\Application\UI\Component::redirect());
exitPoint(\Nette\Application\UI\Component::redirectPermanent());
exitPoint(\Nette\Application\UI\Component::error());
exitPoint(\Nette\Application\UI\Presenter::forward());
exitPoint(\Nette\Application\UI\Presenter::redirectUrl());
exitPoint(\Nette\Application\UI\Presenter::sendJson());
exitPoint(\Nette\Application\UI\Presenter::sendPayload());
exitPoint(\Nette\Application\UI\Presenter::sendResponse());
exitPoint(\Nette\Application\UI\Presenter::sendTemplate());
exitPoint(\Nette\Application\UI\Presenter::terminate());

override(\Nette\Application\UI\Control::createTemplate(0), map(['' => '@']));
