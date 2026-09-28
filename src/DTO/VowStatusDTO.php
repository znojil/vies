<?php
declare(strict_types=1);

namespace Znojil\Vies\DTO;

/**
 * @phpstan-type VowStatusResponseData array{available: bool}
 */
final readonly class VowStatusDTO{

	/**
	 * @param VowStatusResponseData $data
	 */
	public static function fromResponseData(array $data): self{
		return new self(
			$data['available']
		);
	}

	public function __construct(
		public bool $available
	){}

}
