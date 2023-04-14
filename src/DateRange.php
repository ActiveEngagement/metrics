<?php

namespace Actengage\Metrics;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use DateInterval;
use DateTimeZone;
use Illuminate\Support\Traits\Macroable;
use JsonSerializable;
use Throwable;

class DateRange implements JsonSerializable
{
    use Macroable;

    // /**
    //  * The range between the start and end dates.
    //  *
    //  * @var \Actengage\Metrics\Range
    //  */
    // public Range $range;

    /**
     * The starting range value.
     *
     * @var \Carbon\Carbon
     */
    public $start;

    /**
     * The ending range value.
     *
     * @var \Carbon\Carbon
     */
    public $end;

    /**
     * The date interval.
     *
     * @var \Carbon\CarbonInterval
     */
    public $interval;

    /**
     * Construct the date range from a start and end date. The interval is used
     * to calculate the next/prev iterations in the range. If no interval is
     * passed, the time between the start and end dates are used.
     *
     * @param  \DateInterval  $interval
     * @return void
     */
    public function __construct(Carbon $start, Carbon $end, DateInterval $interval = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->interval = CarbonInterval::make($interval ?? $start->diff($end));
    }

    /**
     * Get the next date range in the sequence.
     *
     * @return static
     */
    public function next()
    {
        return new static(
            $start = $this->start->clone()->add($this->interval),
            $start->clone()->subMicrosecond(),
            $this->interval
        );
    }

    /**
     * Get the previous date range in the sequence.
     *
     * @return static
     */
    public function prev()
    {
        return new static(
            $this->start->clone()->sub($this->interval),
            $this->start->clone()->subMicrosecond(),
            $this->interval
        );
    }

    /**
     * Convert the date range into a JSON object.
     */
    public function jsonSerialize(): mixed
    {
        return [
            'start' => $this->start->toString(),
            'end' => $this->end->toString(),
        ];
    }

    /**
     * Boot the class from the ServiceProvider.
     */
    public static function boot(): void
    {
        /**
         * The value range units.
         */
        static::macro('today', function (DateTimeZone|string|null $tz = null) {
            return new static(
                now($tz)->startOfDay(), now($tz), new DateInterval('P1D')
            );
        });

        static::macro('yesterday', function (DateTimeZone|string|null $tz = null) {
            return new static(
                now($tz)->subDay()->startOfDay(), now($tz)->subDay()->endOfDay(), new DateInterval('P1D')
            );
        });

        static::macro('WTD', function (DateTimeZone|string|null $tz = null) {
            return new static(
                now($tz)->startOfWeek(), now($tz), new DateInterval('P7D')
            );
        });

        static::macro('MTD', function (DateTimeZone|string|null $tz = null) {
            return new static(
                now($tz)->startOfMonth(), now($tz), new DateInterval('P1M')
            );
        });

        static::macro('QTD', function (DateTimeZone|string|null $tz = null) {
            return new static(
                now($tz)->startOfQuarter(), now($tz), new DateInterval('P3M')
            );
        });

        static::macro('YTD', function (DateTimeZone|string|null $tz = null) {
            return new static(
                now($tz)->startOfYear(), now($tz), new DateInterval('P1Y')
            );
        });

        static::macro('ALL', function (DateTimeZone|string|null $tz = null) {
            return null;
        });

        /**
         * The trend range units.
         */
        static::macro('minute', function (DateTimeZone|string|null $tz = null) {
            return new static(
                now($tz)->subMinute()->startOfMinute(), now($tz), new DateInterval('PT1M')
            );
        });

        static::macro('hour', function (DateTimeZone|string|null $tz = null) {
            return new static(
                now($tz)->subHour()->startOfHour(), now($tz), new DateInterval('PT1H')
            );
        });

        static::macro('day', function (DateTimeZone|string|null $tz = null) {
            return new static(
                now($tz)->subDay()->startOfDay(), now($tz), new DateInterval('P1D')
            );
        });

        static::macro('week', function (DateTimeZone|string|null $tz = null) {
            return new static(
                now($tz)->subWeek()->startOfDay(), now($tz), new DateInterval('P1W')
            );
        });

        static::macro('month', function (DateTimeZone|string|null $tz = null) {
            return new static(
                now($tz)->subMonthsWithoutOverflow()->startOfDay(), now($tz), new DateInterval('P1M')
            );
        });

        static::macro('quarter', function (DateTimeZone|string|null $tz = null) {
            return new static(
                now($tz)->subQuarter()->startOfDay(), now($tz), new DateInterval('P3M')
            );
        });

        static::macro('year', function (DateTimeZone|string|null $tz = null) {
            return new static(
                now($tz)->subYear()->startOfDay(), now($tz), new DateInterval('P1Y')
            );
        });
    }

    /**
     * Create an instance from a macro or digit.
     *
     * @param  \DateTimeZone|string|null  $tz
     * @return static
     */
    public static function from(string|float|int $interval, DateTimeZone|string|null $tz = null): ?static
    {
        if (isset(static::$macros[$interval])) {
            return static::__callStatic($interval, [$tz]);
        }

        if (is_numeric($interval)) {
            return static::digit($interval, $tz);
        }

        try {
            return static::interval(new DateInterval($interval));
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Create a range from a digit (in days).
     *
     * @param  \DateTimeZone|string|null  $tz
     * @return $this
     */
    public static function digit(string|int $digit, DateTimeZone|string|null $tz = null): static
    {
        return new static(
            now($tz)->subDays($digit)->startOfDay(),
            now($tz)->endOfDay(),
            new DateInterval(sprintf('P%dD', $digit))
        );
    }

    /**
     * Create a range from an inteval (in days).
     *
     * @param  \DateTimeZone|string|null  $tz
     * @return $this
     */
    public static function interval(DateInterval $interval, DateTimeZone|string|null $tz = null): static
    {
        return new static(
            ($end = now($tz))->clone()->sub($interval), $end, $interval
        );
    }
}
