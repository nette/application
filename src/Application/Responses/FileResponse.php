<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\Responses;

use Nette;


/**
 * File download response.
 */
final class FileResponse implements Nette\Application\Response
{
	use Nette\SmartObject;

	/** @var bool */
	public $resuming = true;

	/** @var string */
	private $file;

	/** @var string */
	private $contentType;

	/** @var string */
	private $name;

	/** @var bool */
	private $forceDownload;


	public function __construct(
		string $file,
		string $name = null,
		string $contentType = null,
		bool $forceDownload = true
	) {
		if (!is_file($file) || !is_readable($file)) {
			throw new Nette\Application\BadRequestException("File '$file' doesn't exist or is not readable.");
		}

		$this->file = $file;
		$this->name = $name ?? basename($file);
		$this->contentType = $contentType ?: 'application/octet-stream';
		$this->forceDownload = $forceDownload;
	}


	/**
	 * Returns the path to a downloaded file.
	 */
	public function getFile(): string
	{
		return $this->file;
	}


	/**
	 * Returns the file name.
	 */
	public function getName(): string
	{
		return $this->name;
	}


	/**
	 * Returns the MIME content type of a downloaded file.
	 */
	public function getContentType(): string
	{
		return $this->contentType;
	}


	/**
	 * Sends response to output.
	 */
	public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse): void
	{
		$httpResponse->setContentType($this->contentType);
		$httpResponse->setHeader(
			'Content-Disposition',
			($this->forceDownload ? 'attachment' : 'inline')
				. '; filename="' . $this->name . '"'
				. '; filename*=utf-8\'\'' . rawurlencode($this->name)
		);

		$filesize = $length = filesize($this->file);
		$handle = fopen($this->file, 'r');
		if (!$handle) {
			throw new Nette\Application\BadRequestException("Cannot open file: '{$this->file}'.");
		}

		if ($this->resuming) {
			$httpResponse->setHeader('Accept-Ranges', 'bytes');
			if (preg_match('#^bytes=(\d*)-(\d*)$#D', (string) $httpRequest->getHeader('Range'), $matches)) {
				[, $start, $end] = $matches;
				if ($start === '') {
					$start = max(0, $filesize - $end);
					$end = $filesize - 1;

				} elseif ($end === '' || $end > $filesize - 1) {
					$end = $filesize - 1;
				}
				if ($end < $start) {
					$httpResponse->setCode(416); // requested range not satisfiable
					return;
				}

				$httpResponse->setCode(206);
				$httpResponse->setHeader('Content-Range', 'bytes ' . $start . '-' . $end . '/' . $filesize);
				$length = $end - $start + 1;
				fseek($handle, (int) $start);

			} else {
				$httpResponse->setHeader('Content-Range', 'bytes 0-' . ($filesize - 1) . '/' . $filesize);
			}
		}

		$httpResponse->setHeader('Content-Length', (string) $length);
		while (!feof($handle) && $length > 0) {
			echo $s = fread($handle, min(4000000, $length));
			$length -= strlen($s);
		}
		fclose($handle);
	}
}
