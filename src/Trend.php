<?php

namespace Actengage\Metrics;

use Actengage\Metrics\Contracts\Result as ResultInterface;
use Actengage\Metrics\Expressions\TrendDateExpressionFactory;
use Actengage\Metrics\Results\TrendResult;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTime;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

abstract class Trend extends RangedMetric
{    
    use RoundingPrecision;

    /**
     * Trend metric unit constants.
     */
    const BY_MONTHS = 'month';

    const BY_WEEKS = 'week';

    const BY_DAYS = 'day';

    const BY_HOURS = 'hour';

    const BY_MINUTES = 'minute';
    
    /**
     * Format the date in 12 hour time.
     *
     * @var boolean
     */
    protected bool $twelveHourTime = true;

    /**
     * Return a value result showing a aggregate over time.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  string  $unit
     * @param  string  $function
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\TrendResult
     */
    protected function aggregate($model, $unit, $function, $column, $dateColumn = null)
    {
        $query = $model instanceof Builder ? $model : (new $model)->newQuery();

        $dateColumn = $dateColumn ?? $query->getModel()->getQualifiedCreatedAtColumn();
        
        $expression = (string) TrendDateExpressionFactory::make(
            $query, $dateColumn, $unit, $this->timezone
        );

        $range = $this->getAggregateRange($unit);

        $wrappedColumn = $column instanceof Expression
                ? (string) $column
                : $query->getQuery()->getGrammar()->wrap($column);

        $results = $query
                ->select(DB::raw("{$expression} as date_result, {$function}({$wrappedColumn}) as aggregate"))
                ->where(function($query) use ($dateColumn, $range) {
                    if($range) {
                        $query->whereBetween(
                            $dateColumn, [$range->start, $range->end]
                        );
                    }
                })
                ->groupBy(DB::raw($expression))
                ->orderBy('date_result')
                ->get();

        if(!$range) {
            $range = new DateRange(
                Carbon::make($results->pluck('date_result')->min()),
                now(),
                DateRange::from($unit)->interval
            );
        }

        $possibleDateResults = $this->getAllPossibleDateResults(
            $range,
            $unit,
            $this->twelveHourTime
        );

        $results = array_merge($possibleDateResults, $results->mapWithKeys(function ($result) use ($unit) {
            return [$this->formatAggregateResultDate(
                $result->date_result, $unit, $this->twelveHourTime === 'true'
            ) => round($result->aggregate, $this->roundingPrecision, $this->roundingMode)];
        })->all());

        return $this->result(Arr::last($results))->trend(
            $results
        );
    }

    /**
     * Return a value result showing a count aggregate over time.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  string  $unit
     * @param  string|null  $column
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function count($model, $unit, $column = null)
    {
        $resource = $model instanceof Builder ? $model->getModel() : new $model;

        $column = $column ?? $resource->getQualifiedCreatedAtColumn();

        return $this->aggregate($model, $unit, 'count', $resource->getQualifiedKeyName(), $column);
    }

    /**
     * Return a value result showing a count aggregate over months.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  string|null  $column
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function countByMonths($model, $column = null)
    {
        return $this->count($model, self::BY_MONTHS, $column);
    }    

    /**
     * Return a value result showing a count aggregate over weeks.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  string|null  $column
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function countByWeeks($model, $column = null)
    {
        return $this->count($model, self::BY_WEEKS, $column);
    }

    /**
     * Return a value result showing a count aggregate over days.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  string|null  $column
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function countByDays($model, $column = null)
    {
        return $this->count($model, self::BY_DAYS, $column);
    }

    /**
     * Return a value result showing a count aggregate over hours.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  string|null  $column
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function countByHours($model, $column = null)
    {
        return $this->count($model, self::BY_HOURS, $column);
    }

    /**
     * Return a value result showing a count aggregate over minutes.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  string|null  $column
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function countByMinutes($model, $column = null)
    {
        return $this->count($model, self::BY_MINUTES, $column);
    }

    /**
     * Return a value result showing a average aggregate over months.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function averageByMonths($model, $column, $dateColumn = null)
    {
        return $this->aggregate($model, self::BY_MONTHS, 'avg', $column, $dateColumn);
    }

    /**
     * Return a value result showing a average aggregate over weeks.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function averageByWeeks($model, $column, $dateColumn = null)
    {
        return $this->aggregate($model, self::BY_WEEKS, 'avg', $column, $dateColumn);
    }

    /**
     * Return a value result showing a average aggregate over days.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function averageByDays($model, $column, $dateColumn = null)
    {
        return $this->aggregate($model, self::BY_DAYS, 'avg', $column, $dateColumn);
    }

    /**
     * Return a value result showing a average aggregate over hours.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function averageByHours($model, $column, $dateColumn = null)
    {
        return $this->aggregate($model, self::BY_HOURS, 'avg', $column, $dateColumn);
    }

    /**
     * Return a value result showing a average aggregate over minutes.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function averageByMinutes($model, $column, $dateColumn = null)
    {
        return $this->aggregate($model, self::BY_MINUTES, 'avg', $column, $dateColumn);
    }

    /**
     * Return a value result showing a average aggregate over time.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  string  $unit
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function average($model, $unit, $column, $dateColumn = null)
    {
        return $this->aggregate($model, $unit, 'avg', $column, $dateColumn);
    }

    /**
     * Return a value result showing a sum aggregate over months.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function sumByMonths($model, $column, $dateColumn = null)
    {
        return $this->aggregate($model, self::BY_MONTHS, 'sum', $column, $dateColumn);
    }

    /**
     * Return a value result showing a sum aggregate over weeks.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function sumByWeeks($model, $column, $dateColumn = null)
    {
        return $this->aggregate($model, self::BY_WEEKS, 'sum', $column, $dateColumn);
    }

    /**
     * Return a value result showing a sum aggregate over days.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function sumByDays($model, $column, $dateColumn = null)
    {
        return $this->aggregate($model, self::BY_DAYS, 'sum', $column, $dateColumn);
    }

    /**
     * Return a value result showing a sum aggregate over hours.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function sumByHours($model, $column, $dateColumn = null)
    {
        return $this->aggregate($model, self::BY_HOURS, 'sum', $column, $dateColumn);
    }

    /**
     * Return a value result showing a sum aggregate over minutes.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function sumByMinutes($model, $column, $dateColumn = null)
    {
        return $this->aggregate($model, self::BY_MINUTES, 'sum', $column, $dateColumn);
    }

    /**
     * Return a value result showing a sum aggregate over time.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  string  $unit
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function sum($model, $unit, $column, $dateColumn = null)
    {
        return $this->aggregate($model, $unit, 'sum', $column, $dateColumn);
    }

    /**
     * Return a value result showing a max aggregate over months.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return TrendResult
     */
    public function maxByMonths($model, $column, $dateColumn = null)
    {
        return $this->aggregate($model, self::BY_MONTHS, 'max', $column, $dateColumn);
    }

    /**
     * Return a value result showing a max aggregate over weeks.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function maxByWeeks($model, $column, $dateColumn = null)
    {
        return $this->aggregate($model, self::BY_WEEKS, 'max', $column, $dateColumn);
    }

    /**
     * Return a value result showing a max aggregate over days.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function maxByDays($model, $column, $dateColumn = null)
    {
        return $this->aggregate($model, self::BY_DAYS, 'max', $column, $dateColumn);
    }

    /**
     * Return a value result showing a max aggregate over hours.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function maxByHours($model, $column, $dateColumn = null)
    {
        return $this->aggregate($model, self::BY_HOURS, 'max', $column, $dateColumn);
    }

    /**
     * Return a value result showing a max aggregate over minutes.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function maxByMinutes($model, $column, $dateColumn = null)
    {
        return $this->aggregate($model, self::BY_MINUTES, 'max', $column, $dateColumn);
    }

    /**
     * Return a value result showing a max aggregate over time.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  string  $unit
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function max($model, $unit, $column, $dateColumn = null)
    {
        return $this->aggregate($model, $unit, 'max', $column, $dateColumn);
    }

    /**
     * Return a value result showing a min aggregate over months.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function minByMonths($model, $column, $dateColumn = null)
    {
        return $this->aggregate($model, self::BY_MONTHS, 'min', $column, $dateColumn);
    }

    /**
     * Return a value result showing a min aggregate over weeks.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function minByWeeks($model, $column, $dateColumn = null)
    {
        return $this->aggregate($model, self::BY_WEEKS, 'min', $column, $dateColumn);
    }

    /**
     * Return a value result showing a min aggregate over days.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function minByDays($model, $column, $dateColumn = null)
    {
        return $this->aggregate($model, self::BY_DAYS, 'min', $column, $dateColumn);
    }

    /**
     * Return a value result showing a min aggregate over hours.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function minByHours($model, $column, $dateColumn = null)
    {
        return $this->aggregate($model, self::BY_HOURS, 'min', $column, $dateColumn);
    }

    /**
     * Return a value result showing a min aggregate over minutes.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function minByMinutes($model, $column, $dateColumn = null)
    {
        return $this->aggregate($model, self::BY_MINUTES, 'min', $column, $dateColumn);
    }

    /**
     * Return a value result showing a min aggregate over time.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  string  $unit
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function min($model, $unit, $column, $dateColumn = null)
    {
        return $this->aggregate($model, $unit, 'min', $column, $dateColumn);
    }

    /**
     * Create a new trend metric result.
     *
     * @param  int|float|numeric-string|null  $value
     * @return \Actengage\Metrics\Results\TrendResult
     */
    public function result($value = null): ResultInterface
    {
        return (new TrendResult($this, $value));
    }

    /**
     * Set the `twelveHourTime` property to `true`.
     *
     * @param boolean $twelveHourTime
     * @return $this
     */
    public function twelveHourTime($twelveHourTime = true): static
    {
        $this->twelveHourTime = $twelveHourTime;

        return $this;
    }

    /**
     * Determine the proper aggregate starting date.
     *
     * @param  string  $unit
     * @return \Actengage\Metrics\DateRange|null
     *
     * @throws \InvalidArgumentException
     */
    protected function getAggregateRange($unit): ?DateRange
    {
        $ranges = collect($this->ranges())->keys()->values()->all();

        $range = $this->selectedRangeKey;
        
        if(!$range && $this->range) {
            return $this->range;
        }

        if(count($ranges) > 0 && !in_array($range, $ranges)) {
            $range = min($range ?? max($ranges), max($ranges));
        }

        if(!$range = DateRange::from($this->selectedRangeKey)) {
            return null;
        }
        
        return new DateRange(
            $range->start, $range->end, DateRange::from($unit)->interval
        );
    }
    
    /**
     * Get all of the possible date results for the given units.
     *
     * @param \Actengage\Metrics\DateRange $range
     * @param string $unit
     * @return array<string, int>
     */
    protected function getAllPossibleDateResults(DateRange $range, $unit)
    {
        $possibleDateResults[
            $this->formatPossibleAggregateResultDate($range->start, $unit)
        ] = 0;

        $nextRange = $range;
        
        while ($nextRange->start->lt($endingDate = now())) {
            $nextRange = $nextRange->next();

            if ($nextRange->start->lte($endingDate)) {
                $possibleDateResults[
                    $this->formatPossibleAggregateResultDate($nextRange->start, $unit)
                ] = 0;
            }
        }

        return $possibleDateResults;
    }

    /**
     * Format the aggregate month result date into a proper string.
     *
     * @param  string  $result
     * @return string
     */
    protected function formatAggregateMonthDate($result)
    {
        [$year, $month] = explode('-', $result);

        return with(Carbon::create((int) $year, (int) $month, 1), function ($date) {
            return __($date->format('F')).' '.$date->format('Y');
        });
    }

    /**
     * Format the aggregate week result date into a proper string.
     *
     * @param  string  $result
     * @return string
     */
    protected function formatAggregateWeekDate($result)
    {
        [$year, $week] = explode('-', $result);

        $isoDate = (new DateTime)->setISODate((int) $year, (int) $week)->setTime(0, 0);

        [$startingDate, $endingDate] = [
            Carbon::instance($isoDate),
            Carbon::instance($isoDate)->endOfWeek(),
        ];

        return __($startingDate->format('F')).' '.$startingDate->format('j').' - '.
               __($endingDate->format('F')).' '.$endingDate->format('j');
    }

    /**
     * Format the aggregate result date into a proper string.
     *
     * @param  string  $result
     * @param  string  $unit
     * @param  bool  $twelveHourTime
     * @return string
     */
    protected function formatAggregateResultDate($result, $unit)
    {
        switch ($unit) {
            case 'month':
                return $this->formatAggregateMonthDate($result);

            case 'week':
                return $this->formatAggregateWeekDate($result);

            case 'day':
                return with(Carbon::createFromFormat('Y-m-d', $result), function ($date) {
                    return __($date->format('F')).' '.$date->format('j').', '.$date->format('Y');
                });

            case 'hour':
                return with(Carbon::createFromFormat('Y-m-d H:00', $result), function ($date) {
                    return $this->twelveHourTime
                        ? __($date->format('F')).' '.$date->format('j').' - '.$date->format('g:00 A')
                        : __($date->format('F')).' '.$date->format('j').' - '.$date->format('G:00');
                });

            case 'minute':
            default:
                return with(Carbon::createFromFormat('Y-m-d H:i:00', $result), function ($date) {
                    return $this->twelveHourTime
                        ? __($date->format('F')).' '.$date->format('j').' - '.$date->format('g:i A')
                        : __($date->format('F')).' '.$date->format('j').' - '.$date->format('G:i');
                });
        }
    }

    /**
     * Format the possible aggregate result date into a proper string.
     *
     * @param  \Carbon\CarbonInterface  $date
     * @param  string  $unit
     * @return string
     */
    protected function formatPossibleAggregateResultDate(CarbonInterface $date, $unit)
    {
        switch ($unit) {
            case 'month':
                return __($date->format('F')).' '.$date->format('Y');

            case 'week':
                return __($date->startOfWeek()->format('F')).' '.$date->startOfWeek()->format('j').' - '.
                       __($date->endOfWeek()->format('F')).' '.$date->endOfWeek()->format('j');

            case 'day':
                return __($date->format('F')).' '.$date->format('j').', '.$date->format('Y');

            case 'hour':
                return $this->twelveHourTime
                    ? __($date->format('F')).' '.$date->format('j').' - '.$date->format('g:00 A')
                    : __($date->format('F')).' '.$date->format('j').' - '.$date->format('G:00');

            case 'minute':
            default:
                return $this->twelveHourTime
                    ? __($date->format('F')).' '.$date->format('j').' - '.$date->format('g:i A')
                    : __($date->format('F')).' '.$date->format('j').' - '.$date->format('G:i');
        }
    }
}