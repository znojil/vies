<?php
declare(strict_types=1);

namespace Znojil\Vies\Enum;

/**
 * Canned responses of the check-vat-test-service endpoint.
 *
 * The REST contract does not document these values; the endpoint honors the table from the legacy SOAP test service, which is where the link below points.
 *
 * @link https://ec.europa.eu/taxation_customs/vies/checkVatTestService.wsdl
 */
enum TestVatNumber: string{

	case Valid = '100';

	case Invalid = '200';

	case InvalidInput = '201';

	case InvalidRequesterInfo = '202';

	case ServiceUnavailable = '300';

	case MsUnavailable = '301';

	case Timeout = '302';

	case VatBlocked = '400';

	case IpBlocked = '401';

	case GlobalMaxConcurrentReq = '500';

	case GlobalMaxConcurrentReqTime = '501';

	case MsMaxConcurrentReq = '600';

	case MsMaxConcurrentReqTime = '601';

}
