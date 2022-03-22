<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte\Nodes;

use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;


/**
 * n:nonce
 */
class NNonceNode extends StatementNode
{
	public static function create(): static
	{
		return new static;
	}


	public function print(PrintContext $context): string
	{
		return 'echo $this->global->uiNonce ? " nonce=\"{$this->global->uiNonce}\"" : "";';
	}
}
