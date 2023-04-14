<?php

namespace Actengage\Metrics\Contracts;

use Actengage\Metrics\Metric;
use JsonSerializable;

interface Result extends JsonSerializable
{
    /**
     * Construct the results.
     *
     * @return void
     */
    public function __construct(Metric $metric, mixed $value);
}
