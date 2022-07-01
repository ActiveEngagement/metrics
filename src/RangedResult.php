<?php

namespace Actengage\Metrics;

use Actengage\Metrics\Contracts\Result;

abstract class RangedResult implements Result
{
    /**
     * The metric that generated the result.
     *
     * @var \Actengage\Metrics\Metric $metric
     */
    public Metric $metric;

    /**
     * The value of the result.
     *
     * @var mixed
     */
    public mixed $value;

    /**
     * Construct the results.
     *
     * @param \Actengage\Metrics\Metric $metric
     * @param mixed $value
     * @return void
     */
    public function __construct(Metric $metric, mixed $value)
    {
        $this->metric = $metric;
        $this->value = $value;
    }

    /**
     * Prepare the metric for JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_merge($this->metric->jsonSerialize(), [
            'selected_range_key' => $this->metric->selectedRangeKey,
            'range' => array_merge([
            ], $this->metric->range ? $this->metric->range->jsonSerialize() : [])
        ]);
    }
}
