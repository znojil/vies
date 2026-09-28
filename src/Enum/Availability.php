<?php
declare(strict_types=1);

namespace Znojil\Vies\Enum;

enum Availability: string{

	case Available = 'Available';

	case MonitoringDisabled = 'Monitoring Disabled';

	case Unavailable = 'Unavailable';

}
