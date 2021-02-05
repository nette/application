<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application;

if (false) {
	/** @deprecated use Nette\Routing\Router */
	interface IRouter extends \Nette\Routing\Router
	{
	}

	/** @deprecated use Nette\Application\Response */
	interface IResponse extends Response
	{
	}
} elseif (!interface_exists(IRouter::class)) {
	class_alias(\Nette\Routing\Router::class, IRouter::class);
	class_alias(Response::class, IResponse::class);
}

namespace Nette\Application\UI;

if (false) {
	/** @deprecated use Nette\Application\UI\Renderable */
	interface IRenderable extends Renderable
	{
	}

	/** @deprecated use Nette\Application\UI\SignalReceiver */
	interface ISignalReceiver extends SignalReceiver
	{
	}

	/** @deprecated use Nette\Application\UI\StatePersistent */
	interface IStatePersistent extends StatePersistent
	{
	}

	/** @deprecated use Nette\Application\UI\Template */
	interface ITemplate extends Template
	{
	}

	/** @deprecated use Nette\Application\UI\TemplateFactory */
	interface ITemplateFactory extends TemplateFactory
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
	interface ILatteFactory extends LatteFactory
	{
	}
} elseif (!interface_exists(ILatteFactory::class)) {
	class_alias(LatteFactory::class, ILatteFactory::class);
}
