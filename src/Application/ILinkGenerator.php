<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application;


/**
 * Link generator.
 */
interface ILinkGenerator
{
	/**
	 * Generates URL to presenter.
	 * @param string $dest in format "[[[module:]presenter:]action] [#fragment]"
	 * @throws UI\InvalidLinkException
	 */
	public function link(string $dest, array $params = []): string;

	public function withReferenceUrl(string $url): self;
}
