<?php

namespace Actengage\Metrics\Expressions;

use Illuminate\Database\Grammar;

class SqlSrvTrendDateExpression extends TrendDateExpression
{
    public function __toString()
    {
        return $this->getValue($this->query->getQuery()->getGrammar());
    }

    /**
     * Get the value of the expression.
     *
     * @return mixed
     */
    public function getValue(Grammar $grammar)
    {
        $column = $this->wrap($this->column);
        $offset = $this->offset();

        if ($offset >= 0) {
            $interval = $offset;
        } else {
            $interval = '-'.($offset * -1);
        }

        $date = "DATEADD(hour, {$interval}, {$column})";

        switch ($this->unit) {
            case 'month':
                return "FORMAT({$date}, 'yyyy-MM')";
            case 'week':
                return "concat(
                    YEAR({$date}),
                    '-',
                    datepart(ISO_WEEK, {$date})
                )";
            case 'day':
                return "FORMAT({$date}, 'yyyy-MM-dd')";
            case 'hour':
                return "FORMAT({$date}, 'yyyy-MM-dd HH:00')";
            case 'minute':
                return "FORMAT({$date}, 'yyyy-MM-dd HH:mm:00')";
        }
    }
}
