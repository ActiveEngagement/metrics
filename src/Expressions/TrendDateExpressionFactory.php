<?php

namespace Actengage\Metrics\Expressions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;

class TrendDateExpressionFactory
{
    use Macroable;

    /**
     * Create a new trend expression instance.
     *
     * @param  string  $column
     * @param  string  $unit
     * @param  string  $timezone
     * @return \Actengage\Metrics\TrendDateExpression
     *
     * @throws \InvalidArgumentException
     */
    public static function make(Builder|Relation $query, $column, $unit, $timezone)
    {
        $driver = $query->getConnection()->getDriverName();

        if (static::hasMacro($driver)) {
            return static::$driver($query, $column, $unit, $timezone);
        }

        switch ($driver) {
            case 'sqlite':
                return new SqliteTrendDateExpression($query, $column, $unit, $timezone);
            case 'mysql':
            case 'mariadb':
                return new MySqlTrendDateExpression($query, $column, $unit, $timezone);
            case 'pgsql':
                return new PostgresTrendDateExpression($query, $column, $unit, $timezone);
            case 'sqlsrv':
                return new SqlSrvTrendDateExpression($query, $column, $unit, $timezone);
            default:
                throw new InvalidArgumentException('Trend metric helpers are not supported for this database.');
        }
    }
}
