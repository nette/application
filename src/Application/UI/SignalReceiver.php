<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\UI;


/**
 * Component with ability to receive signal.
 */
interface SignalReceiver
{
	function signalReceived(string $signal): void; // handleSignal
}


interface_exists(ISignalReceiver::class);
