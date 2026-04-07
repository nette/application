<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application;


/**
 * Generates links to presenter actions.
 */
interface LinkGenerator
{
	/**
	 * Generates URL to presenter. Returns null when $mode is 'forward' or 'test'.
	 * @param  string  $destination  in format "[//] [[[module:]presenter:]action | signal! | this | @alias] [#fragment]"
	 * @throws UI\InvalidLinkException
	 */
	function link(
		string $destination,
		array $args = [],
		?UI\Component $component = null,
		?string $mode = null,
	): ?string;


	/**
	 * @param  string  $destination  in format "[[[module:]presenter:]action | signal! | this | @alias]"
	 * @param  string  $mode  forward|redirect|link
	 * @throws UI\InvalidLinkException
	 * @internal
	 */
	function createRequest(
		?UI\Component $component,
		string $destination,
		array $args,
		string $mode,
	): Request;


	/**
	 * Converts Request to URL.
	 */
	function requestToUrl(Request $request, bool $relative = false): string;


	/**
	 * Creates a new instance with a different reference URL.
	 */
	function withReferenceUrl(string $url): static;


	/**
	 * Returns the last created Request.
	 * @internal
	 */
	function getLastRequest(): ?Request;
}
