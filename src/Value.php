<?php

namespace Actengage\Metrics;

use Actengage\Metrics\Contracts\Result as ResultInterface;
use Actengage\Metrics\Results\ValueResult;
use Illuminate\Contracts\Database\Eloquent\Builder;

abstract class Value extends RangedMetric
{
    use RoundingPrecision;

    /**
     * Return a value result showing the growth of a model over a given time frame.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  string  $function
     * @param  \Illuminate\Database\Query\Expression|string|null  $column
     * @param  string|null  $dateColumn
     */
    protected function aggregate($model, $function, $column = null, $dateColumn = null): ValueResult
    {
       $query = $model instanceof Builder ? $model : (new $model)->newQuery();

       $column = $column ?? $query->getModel()->getQualifiedKeyName();

        if ($this->range === null) {
            return $this->result(
                round(
                    with(clone $query)->{$function}($column),
                   $this->roundingPrecision,
                   $this->roundingMode
                )
            );
        }

       $dateColumn = $dateColumn ?? $query->getModel()->getQualifiedCreatedAtColumn();

       $previousValue = round(
            with(clone $query)->whereBetween(
               $dateColumn, [$this->range->prev()->start, $this->range->prev()->end]
            )->{$function}($column) ?? 0,
           $this->roundingPrecision,
           $this->roundingMode
        );

        return $this->result(
            round(
                with(clone $query)->whereBetween(
                   $dateColumn, [$this->range->start, $this->range->end]
                )->{$function}($column) ?? 0,
               $this->roundingPrecision,
               $this->roundingMode
            )
        )->previous($previousValue);
    }

    /**
     * Return a value result showing the growth of an average aggregate over time.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string|null  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\ValueResult
     */
    public function average($model, $column, $dateColumn = null)
    {
        return $this->aggregate($model, 'avg', $column, $dateColumn);
    }

    /**
     * Return a value result showing the growth of an count aggregate over time.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string|null  $column
     * @param  string|null  $dateColumn
     */
    public function count($model, $column = null, $dateColumn = null): ValueResult
    {
        return $this->aggregate($model, 'count', $column, $dateColumn);
    }

    /**
     * Return a value result showing the growth of a maximum aggregate over time.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string|null  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\ValueResult
     */
    public function max($model, $column, $dateColumn = null)
    {
        return $this->aggregate($model, 'max', $column, $dateColumn);
    }

    /**
     * Return a value result showing the growth of a minimum aggregate over time.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string|null  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\ValueResult
     */
    public function min($model, $column, $dateColumn = null)
    {
        return $this->aggregate($model, 'min', $column, $dateColumn);
    }

    /**
     * Instantiate a Result using the given value.
     *
     * @return \Actengage\Metrics\Contracts\Result;
     */
    public function result(mixed $value): ResultInterface
    {
        return new ValueResult($this, $value);
    }

    /**
     * Return a value result showing the growth of a sum aggregate over time.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Illuminate\Database\Query\Expression|string|null  $column
     * @param  string|null  $dateColumn
     * @return \Actengage\Metrics\Results\ValueResult
     */
    public function sum($model, $column, $dateColumn = null)
    {
        return $this->aggregate($model, 'sum', $column, $dateColumn);
    }
}
