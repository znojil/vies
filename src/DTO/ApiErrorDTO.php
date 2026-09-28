<?php
declare(strict_types=1);

namespace Znojil\Vies\DTO;

/**
 * The API leaves message out for most error codes, e.g. {"error": "MS_UNAVAILABLE"}.
 *
 * @phpstan-type ApiErrorResponseData array{error: string, message?: string}
 */
final readonly class ApiErrorDTO{

	/**
	 * @param ApiErrorResponseData $data
	 */
	public static function fromResponseData(array $data): self{
		return new self(
			$data['error'],
			\Znojil\Vies\Internal\StringUtil::normalizeNullableProperty($data['message'] ?? null)
		);
	}

	public function __construct(
		public string $code,
		public ?string $message
	){}

}
