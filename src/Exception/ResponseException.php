<?php
declare(strict_types=1);

namespace Znojil\Vies\Exception;

class ResponseException extends \RuntimeException implements Exception{

	/**
	 * @param int $statusCode HTTP status code, 0 when the error was not signalled by the HTTP status
	 */
	public function __construct(
		string $message,
		public readonly int $statusCode = 0,
		public readonly ?string $responseBody = null,
		?\Throwable $previous = null
	){
		parent::__construct($message, $statusCode, $previous);
	}

}
