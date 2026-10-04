# Znojil VIES

[![Latest Stable Version](https://img.shields.io/packagist/v/znojil/vies)](https://packagist.org/packages/znojil/vies)
[![PHP Version Require](https://img.shields.io/packagist/dependency-v/znojil/vies/php)](https://packagist.org/packages/znojil/vies)
[![License](https://img.shields.io/packagist/l/znojil/vies)](LICENSE)
[![Tests](https://github.com/znojil/vies/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/znojil/vies/actions/workflows/tests.yml)

A simple and modern PHP library for communicating with the [VIES VAT number validation service](https://ec.europa.eu/taxation_customs/vies/) of the European Commission.

Covers all endpoints of the [VIES REST API](https://ec.europa.eu/assets/taxud/vow-information/swagger_publicVAT.yaml), with typed DTOs, enums and full PHPStan level max coverage. No API key or registration is needed — VIES is a public service.

## 🚀 Installation

```bash
composer require znojil/vies
```

## 📖 Usage

### 1. Checking a VAT Number

```php
use Znojil\Vies\Client;
use Znojil\Vies\Enum\Country;
use Znojil\Vies\Request\CheckVatNumberRequest;

$client = new Client;

$result = $client->send(new CheckVatNumberRequest(Country::CzechRepublic, '12345674'));
$result->valid; // true
$result->name; // 'Example s.r.o.'
$result->address; // "Příkladná 1234/5\nPRAHA 1 - NOVÉ MĚSTO\n110 00  PRAHA 1"
$result->requestDate; // DateTimeImmutable
```

The VAT number is passed without the country prefix — the country is a separate `Country` enum argument. To get there from what users type, see [Parsing User Input](#2-parsing-user-input).

Member states are free to withhold the name and address even for a valid number, so `name` and `address` are `null` whenever the service returns no value. Do not treat a missing name as an invalid number — `valid` is the answer.

The address is free text from the member state's register and its layout differs by member state. `getAddressLines()` splits it into lines without interpreting them:

```php
$result->getAddressLines(); // ['Příkladná 1234/5', 'PRAHA 1 - NOVÉ MĚSTO', '110 00  PRAHA 1']
```

To confirm individual address parts, use [approximate matching](#3-approximate-matching) instead of parsing the lines.

### 2. Parsing User Input

`VatNumber::parse()` turns a VAT number as people write it into the country and the national part the request expects. It removes spaces, dots and dashes, uppercases the input and splits off the country prefix. `GR` is accepted for Greece and converted to `EL`, the code VIES uses.

```php
use Znojil\Vies\VatNumber;

$vat = VatNumber::parse('cz 123 456 74');

$vat->country; // Country::CzechRepublic
$vat->number; // '12345674'
(string) $vat; // 'CZ12345674'

$result = $client->send(new CheckVatNumberRequest($vat->country, $vat->number));
```

When the input has no prefix, pass the country. A prefix in the input must then match it, so a form expecting a Czech number rejects a Slovak one:

```php
VatNumber::parse('12345674', Country::CzechRepublic); // CZ12345674
VatNumber::parse('CZ12345674', Country::CzechRepublic); // CZ12345674 as well
VatNumber::parse('SK1234567890', Country::CzechRepublic); // throws InvalidArgumentException
```

France is the exception: its numbers may themselves start with two letters, so for France only `FR` is treated as a prefix — `parse('AT123456789', Country::France)` gives `FRAT123456789`, not a mismatch with Austria.

`InvalidArgumentException` is thrown when the input has no prefix and no country is given, when the prefix is not a supported country (e.g. `GB`), when the prefix does not match the given country, or when the national part does not match the generic format VIES accepts — 2 to 12 letters, digits, `+` or `*`.

> `parse()` does not apply any country specific rules: no length or check digit validation, no zero padding. A number that parses can still be invalid, and only VIES can tell. It works without a client, so it can also be used on its own, e.g. to normalize form input.

### 3. Approximate Matching

Supplying trader details asks VIES to compare them with the registered ones. To also get a `requestIdentifier` — the consultation number proving the check was made — supply your own VAT number as the requester:

```php
$result = $client->send(new CheckVatNumberRequest(
	country: Country::CzechRepublic,
	vatNumber: '12345674',
	requesterCountry: Country::CzechRepublic,
	requesterVatNumber: '...', // your own VAT number
	traderName: 'Example s.r.o.',
	traderCity: 'Praha'
));

$result->traderNameMatch; // ApproximateMatch enum
$result->traderCityMatch; // ApproximateMatch enum
$result->traderStreetMatch; // ApproximateMatch::NotProcessed — not submitted
$result->requestIdentifier; // ?string
```

Fields you do not submit come back as `NotProcessed`. Matching is advisory: member states differ in how strictly they compare, and `Invalid` does not by itself mean the VAT number is invalid.

### 4. Checking Service Status

```php
use Znojil\Vies\Request\CheckStatusRequest;

$status = $client->send(new CheckStatusRequest);

$status->vow->available; // true — VIES itself is up

foreach($status->countries as $country){
	$country->country; // Country enum
	$country->availability; // Availability enum
}
```

Worth checking before a batch run — an unavailable member state cannot be validated no matter how many times you ask.

### 5. Testing Error Handling

VIES provides a test endpoint that returns canned responses, so error handling can be exercised without depending on a member state actually being down:

```php
use Znojil\Vies\Enum\TestVatNumber;
use Znojil\Vies\Request\CheckVatNumberTestRequest;

$client->send(new CheckVatNumberTestRequest(Country::CzechRepublic, TestVatNumber::Valid));
// CheckedVatNumberDTO with valid = true

$client->send(new CheckVatNumberTestRequest(Country::CzechRepublic, TestVatNumber::MsUnavailable));
// throws ApiException with message 'MS_UNAVAILABLE'
```

Each `TestVatNumber` case other than `Valid` and `Invalid` produces the error code of the same name. The responses are abridged — no trader data is returned — which the DTO handles.

### 6. Using a Custom HTTP Client

By default the library uses [znojil/http](https://github.com/znojil/http). You can inject your own implementation as the first argument to the `Client` constructor. It must implement the `Znojil\Vies\Http\Client` interface.

```php
use Znojil\Vies\Client;
use Znojil\Vies\Http\Client as ViesHttpClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class MyCustomHttpClient implements ViesHttpClient{
	public function send(string $method, string|UriInterface $uri, array $headers = [], mixed $data = null, array $options = []): ResponseInterface{
		// your implementation
	}
}

$client = new Client(new MyCustomHttpClient);
```

`$data` is encoded according to the `Content-Type` header (JSON or form); an empty array or `null` means no content. String keys in `$options` are transport-agnostic options defined by this library — your implementation must honor them and reject any other string key with an exception. Integer keys are raw `CURLOPT_*` constants — non-cURL implementations must reject them with an exception rather than silently ignore them, so that a consumer never ends up with options that silently don't apply.

## ⚠️ Error Handling

VIES reports most errors with **HTTP status 200** and an error payload rather than with a 4xx/5xx status. The client detects those and throws an exception, so a successful `send()` always means a real answer.

The client throws exceptions to help you identify the issue:

- `Znojil\Vies\Exception\ApiException`: For errors VIES reports in the body of a successful response (e.g. `MS_UNAVAILABLE`). Contains the reported `errors`, each with a `code` and an optional `message`.
- `Znojil\Vies\Exception\ClientException`: For HTTP client-side errors (4xx). When the body carries an error payload, `getPrevious()` is an `ApiException`.
- `Znojil\Vies\Exception\ServerException`: For HTTP server-side errors (5xx). When the body carries an error payload, `getPrevious()` is an `ApiException`.
- `Znojil\Vies\Exception\ResponseException`: For other unsuccessful HTTP responses. The base class of the three above and of `JsonResponseException` — it carries `statusCode` and the raw `responseBody`.
- `Znojil\Vies\Exception\JsonException`: When a response body is not valid JSON.
- `Znojil\Vies\Exception\JsonResponseException`: When a response body is valid JSON but not an object or array (subtype of `ResponseException`).
- `Znojil\Vies\Exception\InvalidArgumentException`: For invalid input (e.g. a VAT number `VatNumber::parse()` cannot handle).

```php
use Znojil\Vies\Exception\ApiException;
use Znojil\Vies\Exception\ServerException;

try{
	$result = $client->send(new CheckVatNumberRequest(Country::Germany, '123456789'));
}catch(ApiException $e){
	echo $e->getMessage(); // MS_UNAVAILABLE

	foreach($e->errors as $error){
		$error->code; // 'MS_UNAVAILABLE'
		$error->message; // ?string — the API leaves it out for most codes
	}
}catch(ServerException $e){
	// VIES server error (5xx)
	$e->getPrevious(); // ?ApiException
}
```

> All exceptions thrown by the library implement the `Znojil\Vies\Exception\Exception` marker interface, so a single catch can cover them all.

### Error Codes

The REST contract does not list the error codes. These are the ones known from the VIES SOAP service definitions; the descriptions come from [checkVatService.wsdl](https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl):

| Code | Meaning |
|---|---|
| `INVALID_INPUT` | the country code is invalid or the VAT number is empty |
| `SERVICE_UNAVAILABLE` | network or web application error — try again later |
| `MS_UNAVAILABLE` | the member state application is not replying — try again later |
| `TIMEOUT` | no reply within the allocated time — try again later |
| `GLOBAL_MAX_CONCURRENT_REQ` | too many concurrent requests to VIES — try again later |
| `MS_MAX_CONCURRENT_REQ` | too many concurrent requests to the member state — try again later |
| `INVALID_REQUESTER_INFO` | no official description |
| `VAT_BLOCKED` | no official description |
| `IP_BLOCKED` | no official description |
| `GLOBAL_MAX_CONCURRENT_REQ_TIME` | no official description |
| `MS_MAX_CONCURRENT_REQ_TIME` | no official description |

Other codes can appear, e.g. `VOW-ERR-1` with HTTP 500 for a malformed request.

### Fair Use

VIES is a free public service with no published rate limit, but it does throttle: `GLOBAL_MAX_CONCURRENT_REQ` and `MS_MAX_CONCURRENT_REQ` are returned when too many requests arrive at once. Validate sequentially, cache results, and back off when you receive these, `MS_UNAVAILABLE` or `TIMEOUT`.

## 📄 License

This library is open-source software licensed under the [MIT license](https://choosealicense.com/licenses/mit/).
