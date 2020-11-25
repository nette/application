<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\Responses;

use Nette;


/**
 * String output response.
 */
final class TextResponse implements Nette\Application\Response
{
	use Nette\SmartObject;

	/** @var mixed */
	private $source;


	/**
	 * @param  mixed  $source
	 */
	public function __construct($source)
	{
		$this->source = $source;
	}


	/**
	 * @return mixed
	 */
	public function getSource()
	{
		return $this->source;
	}


	/**
	 * Sends response to output.
	 */
	public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse): void
	{
		if ($this->source instanceof Nette\Application\UI\Template) {
			$this->source->render();

		} else {
			echo $this->source;
		}
	}
}
