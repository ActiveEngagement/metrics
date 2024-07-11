<?php

namespace Actengage\Metrics;

use Actengage\Metrics\Results\PartitionResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

abstract class Partition extends Metric
{
    use RoundingPrecision;

    /**
     * Return a partition result showing the segments of a count aggregate.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  string  $groupBy
     * @param  \Illuminate\Database\Query\Expression|string|null  $column
     * @return \Actengage\Metrics\Results\PartitionResult
     */
    public function count($model, $groupBy, $column = null): PartitionResult
    {
        return $this->aggregate($model, 'count', $column, $groupBy);
    }

    /**
     * Return a partition result showing the segments of an average aggregate.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string|null  $column
     * @param  string  $groupBy
     * @return \Actengage\Metrics\Results\PartitionResult
     */
    public function average($model, $column, $groupBy): PartitionResult
    {
        return $this->aggregate($model, 'avg', $column, $groupBy);
    }

    /**
     * Return a partition result showing the segments of a sum aggregate.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string|null  $column
     * @param  string  $groupBy
     * @return \Actengage\Metrics\Results\PartitionResult
     */
    public function sum($model, $column, $groupBy): PartitionResult
    {
        return $this->aggregate($model, 'sum', $column, $groupBy);
    }

    /**
     * Return a partition result showing the segments of a max aggregate.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string|null  $column
     * @param  string  $groupBy
     * @return \Actengage\Metrics\Results\PartitionResult
     */
    public function max($model, $column, $groupBy): PartitionResult
    {
        return $this->aggregate($model, 'max', $column, $groupBy);
    }

    /**
     * Return a partition result showing the segments of a min aggregate.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string|null  $column
     * @param  string  $groupBy
     * @return \Actengage\Metrics\Results\PartitionResult
     */
    public function min($model, $column, $groupBy): PartitionResult
    {
        return $this->aggregate($model, 'min', $column, $groupBy);
    }

    /**
     * Create a new partition metric result.
     *
     * @param  array<string, int|float>  $value
     * @return \Actengage\Metrics\Results\PartitionResult
     */
    public function result(mixed $value): PartitionResult
    {
        return new PartitionResult(collect($value)->map(function ($number) {
            return round($number, $this->roundingPrecision, $this->roundingMode);
        })->toArray());
    }

    /**
     * Return a partition result showing the segments of a aggregate.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  string  $function
     * @param  \Illuminate\Database\Query\Expression|string|null  $column
     * @param  string  $groupBy
     * @return \Actengage\Metrics\Results\PartitionResult
     */
    protected function aggregate($model, $function, $column, $groupBy): PartitionResult
    {
        $query = $model instanceof Builder ? $model : (new $model)->newQuery();

        $wrappedColumn = $query->getQuery()->getGrammar()->wrap($column ?? $query->getModel()->getQualifiedKeyName());

        $results = $query->select(
            $groupBy, DB::raw("{$function}({$wrappedColumn}) as aggregate")
        )->groupBy($groupBy)->get();

        return $this->result($results->mapWithKeys(function ($result) use ($groupBy) {
            return $this->formatAggregateResult($result, $groupBy);
        })->all());
    }

    /**
     * Format the aggregate result for the partition.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $result
     * @param  string  $groupBy
     * @return array<string|int, int|float>
     */
    protected function formatAggregateResult($result, $groupBy)
    {
        $key = with($result->{last(explode('.', $groupBy))}, function ($key) {
            return value($key);
        });

        if (! is_int($key)) {
            $key = (string) $key;
        }

        return [$key => $result->aggregate];
    }
}
