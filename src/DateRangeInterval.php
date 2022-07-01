<?php

namespace Actengage\Metrics;

use Carbon\CarbonInterval;
use UnhandledMatchError;
use ValueError;

enum Da: string {
    
    case TODAY = 'TODAY';
    case YESTERDAY = 'YESTERDAY';
    case WTD = 'WTD';
    case MTD = 'MTD';
    case QTD = 'QTD';
    case YTD = 'YTD';

    public function interval(): CarbonInterval
    {
        return match($this) {
            static::TODAY => new CarbonInterval('P1D'),
            static::YESTERDAY => new CarbonInterval('P1D'),
            static::WTD => new CarbonInterval('P1W'),
            static::MTD => new CarbonInterval('P1M'),
            static::QTD => new CarbonInterval('P1M'),
            static::YTD => new CarbonInterval('P1Y'),
        };
    }

    /**
     * Create a CarbonInterval from the given value.
     *
     * @param string|int $interval
     * @throws \ValueError
     * @return \CarbonInterval|null
     */
    public static function make(string|int $interval): ?CarbonInterval
    {
        try {
            return static::from(strtoupper($interval));
        }
        catch(ValueError $e) {
            return null;
        }
    }
}