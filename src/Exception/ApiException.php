<?php
declare(strict_types=1);

namespace Znojil\Vies\Exception;

use Znojil\Vies\DTO\ApiErrorDTO;

/**
 * @phpstan-import-type ApiErrorResponseData from ApiErrorDTO
 */
class ApiException extends ResponseException{

	/**
	 * @param array<int|string, mixed> $data decoded response body
	 * @throws self
	 */
	public static function throwOnError(array $data, int $statusCode = 0, ?string $responseBody = null): void{
		// {"actionSucceed": false, "errorWrappers": [{"error": "...", "message": "..."}, ...]}
		if(!isset($data['errorWrappers']) || !is_array($data['errorWrappers'])){
			return;
		}

		$errors = [];
		foreach($data['errorWrappers'] as $errorWrapper){
			if(is_array($errorWrapper)){
				/** @var ApiErrorResponseData $errorWrapper */
				$errors[] = ApiErrorDTO::fromResponseData($errorWrapper);
			}
		}

		throw new self(
			$errors,
			$errors === []
				? 'Unknown error.' // errorWrappers present but empty or malformed
				: implode("\n", array_map(
					static fn(ApiErrorDTO $error): string => $error->message !== null ? "$error->message ($error->code)" : $error->code,
					$errors
				)),
			$statusCode,
			$responseBody
		);
	}

	/**
	 * @param list<ApiErrorDTO> $errors
	 */
	public function __construct(
		public readonly array $errors,
		string $message,
		int $statusCode = 0,
		?string $responseBody = null,
		?\Throwable $previous = null
	){
		parent::__construct($message, $statusCode, $responseBody, $previous);
	}

}
