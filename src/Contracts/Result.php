<?php

namespace Actengage\Metrics\Contracts;

use Actengage\Metrics\Metric;
use JsonSerializable;

interface Result extends JsonSerializable
{
    /**
     * Construct the results.
     *
     * @param \Actengage\Metrics\Metric $metric
     * @param mixed $value
     * @return void
     */
    public function __construct(Metric $metric, mixed $value);
}