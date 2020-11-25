<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application;

if (false) {
	/** @deprecated use Nette\Routing\Router */
	interface IRouter
	{
	}

	/** @deprecated use Nette\Application\Response */
	interface IResponse
	{
	}
} elseif (!interface_exists(IRouter::class)) {
	class_alias(\Nette\Routing\Router::class, IRouter::class);
	class_alias(Response::class, IResponse::class);
}

namespace Nette\Application\UI;

if (false) {
	/** @deprecated use Nette\Application\UI\Renderable */
	interface IRenderable
	{
	}

	/** @deprecated use Nette\Application\UI\SignalReceiver */
	interface ISignalReceiver
	{
	}

	/** @deprecated use Nette\Application\UI\StatePersistent */
	interface IStatePersistent
	{
	}

	/** @deprecated use Nette\Application\UI\Template */
	interface ITemplate
	{
	}

	/** @deprecated use Nette\Application\UI\TemplateFactory */
	interface ITemplateFactory
	{
	}
} elseif (!interface_exists(IRenderable::class)) {
	class_alias(Renderable::class, IRenderable::class);
	class_alias(SignalReceiver::class, ISignalReceiver::class);
	class_alias(StatePersistent::class, IStatePersistent::class);
	class_alias(Template::class, ITemplate::class);
	class_alias(TemplateFactory::class, ITemplateFactory::class);
}

namespace Nette\Bridges\ApplicationLatte;

if (false) {
	/** @deprecated use Nette\Bridges\ApplicationLatte\LatteFactory */
	interface ILatteFactory
	{
	}
} elseif (!interface_exists(ILatteFactory::class)) {
	class_alias(LatteFactory::class, ILatteFactory::class);
}
