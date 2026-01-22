<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\Responses;

use Nette;
use function strlen;


/**
 * File download response.
 */
final class FileResponse implements Nette\Application\Response
{
	public bool $resuming = true;
	private readonly string $name;


	public function __construct(
		private readonly string $file,
		?string $name = null,
		private readonly string $contentType = 'application/octet-stream',
		private readonly bool $forceDownload = true,
	) {
		if (!is_file($file) || !is_readable($file)) {
			throw new Nette\Application\BadRequestException("File '$file' doesn't exist or is not readable.");
		}

		$this->name = $name ?? basename($file);
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
				. '; filename*=utf-8\'\'' . rawurlencode($this->name),
		);

		$filesize = $length = filesize($this->file);
		if ($filesize === false) {
			throw new Nette\Application\BadRequestException("Cannot stat file: '{$this->file}'.");
		}
		$handle = fopen($this->file, 'r') ?: throw new Nette\Application\BadRequestException("Cannot open file: '{$this->file}'.");

		if ($this->resuming) {
			$httpResponse->setHeader('Accept-Ranges', 'bytes');
			if (preg_match('#^bytes=(\d+)?-(\d+)?$#D', (string) $httpRequest->getHeader('Range'), $matches, PREG_UNMATCHED_AS_NULL)) {
				[, $start, $end] = $matches;
				if ($start === null) {
					$start = max(0, $filesize - (int) $end);
					$end = $filesize - 1;

				} else {
					$start = (int) $start;
					$end = $end === null || (int) $end > $filesize - 1
						? $filesize - 1
						: (int) $end;
				}

				if ($end < $start) {
					$httpResponse->setCode(416); // requested range not satisfiable
					return;
				}

				$httpResponse->setCode(206);
				$httpResponse->setHeader('Content-Range', 'bytes ' . $start . '-' . $end . '/' . $filesize);
				$length = $end - $start + 1;
				fseek($handle, $start);

			} else {
				$httpResponse->setHeader('Content-Range', 'bytes 0-' . ($filesize - 1) . '/' . $filesize);
			}
		}

		$httpResponse->setHeader('Content-Length', (string) $length);
		while (!feof($handle) && $length > 0) {
			$s = fread($handle, min(4_000_000, $length));
			if ($s === false) {
				throw new Nette\IOException("Cannot read file '$this->file'.");
			}
			echo $s;
			$length -= strlen($s);
		}

		fclose($handle);
	}
}
