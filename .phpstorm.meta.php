<?php

declare(strict_types=1);

namespace PHPSTORM_META;

expectedArguments(\Nette\Application\Routers\Route::__construct(), 2, \Nette\Routing\Router::ONE_WAY);
expectedArguments(\Nette\Application\Routers\SimpleRouter::__construct(), 1, \Nette\Routing\Router::ONE_WAY);

registerArgumentsSet('nette_http_codes_3xx',
	\Nette\Http\IResponse::S300_MultipleChoices,
	\Nette\Http\IResponse::S301_MovedPermanently,
	\Nette\Http\IResponse::S302_Found,
	\Nette\Http\IResponse::S303_SeeOther,
	\Nette\Http\IResponse::S303_PostGet,
	\Nette\Http\IResponse::S304_NotModified,
	\Nette\Http\IResponse::S305_UseProxy,
	\Nette\Http\IResponse::S307_TemporaryRedirect,
	\Nette\Http\IResponse::S308_PermanentRedirect,
);
registerArgumentsSet('nette_http_codes_4xx',
	\Nette\Http\IResponse::S400_BadRequest,
	\Nette\Http\IResponse::S401_Unauthorized,
	\Nette\Http\IResponse::S402_PaymentRequired,
	\Nette\Http\IResponse::S403_Forbidden,
	\Nette\Http\IResponse::S404_NotFound,
	\Nette\Http\IResponse::S405_MethodNotAllowed,
	\Nette\Http\IResponse::S406_NotAcceptable,
	\Nette\Http\IResponse::S407_ProxyAuthenticationRequired,
	\Nette\Http\IResponse::S408_RequestTimeout,
	\Nette\Http\IResponse::S409_Conflict,
	\Nette\Http\IResponse::S410_Gone,
	\Nette\Http\IResponse::S411_LengthRequired,
	\Nette\Http\IResponse::S412_PreconditionFailed,
	\Nette\Http\IResponse::S413_RequestEntityTooLarge,
	\Nette\Http\IResponse::S414_RequestUriTooLong,
	\Nette\Http\IResponse::S415_UnsupportedMediaType,
	\Nette\Http\IResponse::S416_RequestedRangeNotSatisfiable,
	\Nette\Http\IResponse::S417_ExpectationFailed,
	\Nette\Http\IResponse::S421_MisdirectedRequest,
	\Nette\Http\IResponse::S422_UnprocessableEntity,
	\Nette\Http\IResponse::S423_Locked,
	\Nette\Http\IResponse::S424_FailedDependency,
	\Nette\Http\IResponse::S426_UpgradeRequired,
	\Nette\Http\IResponse::S428_PreconditionRequired,
	\Nette\Http\IResponse::S429_TooManyRequests,
	\Nette\Http\IResponse::S431_RequestHeaderFieldsTooLarge,
	\Nette\Http\IResponse::S451_UnavailableForLegalReasons,
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
