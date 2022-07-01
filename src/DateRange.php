<?php

namespace Actengage\Metrics;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use DateInterval;
use Illuminate\Support\Traits\Macroable;
use JsonSerializable;
use Throwable;

class DateRange implements JsonSerializable {

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
     * @param \Actengage\Metrics\Range $range
     * @param \Carbon\Carbon $start
     * @param \Carbon\Carbon $end
     * @param \DateInterval $interval
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
     *
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return [
            'start' => $this->start->toString(),
            'end' => $this->end->toString()
        ];
    }

    /**
     * Boot the class from the ServiceProvider.
     *
     * @return void
     */
    public static function boot(): void
    {
        /**
         * The value range units.
         */
        static::macro('today', function() {
            return new static(
                now()->startOfDay(), now(), new DateInterval('P1D') 
            );
        });

        static::macro('yesterday', function() {
            return new static(
                now()->subDay()->startOfDay(), now()->subDay()->endOfDay(), new DateInterval('P1D') 
            );
        });

        static::macro('WTD', function() {
            return new static(
                now()->startOfWeek(), now(), new DateInterval('P7D') 
            );
        });
        
        static::macro('MTD', function() {
            return new static(
                now()->startOfMonth(), now(), new DateInterval('P1M') 
            );
        });
        
        static::macro('QTD', function() {
            return new static(
                now()->startOfQuarter(), now(), new DateInterval('P3M') 
            );
        });
        
        static::macro('YTD', function() {
            return new static(
                now()->startOfYear(), now(), new DateInterval('P1Y') 
            );
        });
        
        static::macro('ALL', function() {
            return null;
        });
        
        /**
         * The trend range units.
         */
        static::macro('minute', function(int $offset = 1) {
            return new static(now()->subMinute($offset), now(), new DateInterval('PT1M'));
        });

        static::macro('hour', function(int $offset = 1) {
            return new static(now()->subHour($offset), now(), new DateInterval('PT1H'));
        });
        
        static::macro('day', function(int $offset = 1) {
            return new static(now()->subDay($offset), now(), new DateInterval('P1D'));
        });
        
        static::macro('week', function(int $offset = 1) {
            return new static(now()->subWeek($offset), now(), new DateInterval('P1W'));
        });
        
        static::macro('month', function(int $offset = 1) {
            return new static(now()->subMonthsWithoutOverflow($offset), now(), new DateInterval('P1M'));
        });
        
        static::macro('quarter', function(int $offset = 1) {
            return new static(now()->subQuarter($offset), now(), new DateInterval('P3M'));
        });
        
        static::macro('year', function(int $offset = 1) {
            return new static(now()->subYear($offset), now(), new DateInterval('P1Y'));
        });
        
        /**
         * Numerical range units (in days)
         */
        static::macro('digit', function(string|float|int $interval) {
            return new static(
                now()->subDays($interval)->startOfDay(),
                now()->endOfDay(),
                new DateInterval(sprintf('P%dD', $interval))
            );
        });
        
        static::macro('interval', function(DateInterval $interval) {
            return new static(
                ($end = now())->clone()->sub($interval), $end, $interval
            );
        });
    }

    /**
     * Create an instance from a macro or digit.
     *
     * @param string|float|integer $interval
     * @param mixed ...$args
     * @return static
     */
    public static function from(string|float|int $interval, ...$args): ?static
    {
        if(isset(static::$macros[$interval])) {
            return static::__callStatic($interval, $args);
        }

        if(is_numeric($interval)) {
            return static::digit($interval, ...$args);
        }

        try {
            return static::interval(new DateInterval($interval));
        }
        catch(Throwable $e) {
            throw $e;
        }

        // throw new BadMethodCallException(sprintf(
        //     'Method %s::%s does not exist.', static::class, $interval
        // ));
    }
}