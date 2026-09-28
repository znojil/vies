<?php
declare(strict_types=1);

namespace Znojil\Vies\Enum;

enum ApproximateMatch: string{

	case Invalid = 'INVALID';

	case NotProcessed = 'NOT_PROCESSED';

	case Valid = 'VALID';

}
