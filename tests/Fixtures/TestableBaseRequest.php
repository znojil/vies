<?php
declare(strict_types=1);

namespace Znojil\Vies\Tests\Fixtures;

use Znojil\Vies\Request\BaseRequest;

/**
 * @extends BaseRequest<null>
 */
final class TestableBaseRequest extends BaseRequest{

	public function getMethod(): string{
		return 'GET';
	}

	public function getUrn(): string{
		return 'test';
	}

	public function getData(): array{
		return $this->buildData([
			'string' => 'String',
			'int' => 123,
			'float' => 123.45,
			'bool' => true,
			'null' => null,
			'false' => false,
			'zero' => 0,
			'empty_string' => ''
		]);
	}

	public function createResponse(\Psr\Http\Message\ResponseInterface $httpResponse): null{
		return null;
	}

	public function parseJsonResponseBody(string $responseBody): array{
		return parent::parseJsonResponseBody($responseBody);
	}

}
