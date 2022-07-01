<?php

namespace Actengage\Metrics;

abstract class RangedMetric extends Metric
{
    /**
     * The ranges available for the metric.
     *
     * @var array<string|int, string>
     */
    public array $ranges = [];

    /**
     * The selected range key.
     *
     * @var \Actengage\Metrics\DateRange|null
     */
    public ?DateRange $range = null;

    /**
     * The selected range key.
     *
     * @var string|int|null
     */
    public string|int|null $selectedRangeKey = null;

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges(): array
    {
        return $this->ranges ?? [];
    }

    /**
     * Set the default range.
     *
     * @param string|int|null $key
     * @return $this
     */
    public function range(string|int|null $key)
    {
        if($this->selectedRangeKey = $key) {
            $this->range = DateRange::from($key);
        }
        else {
            $this->range = null;
        }
        
        return $this;
    }

    /**
     * Prepare the metric for JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'range' => $this->range,
            'ranges' => collect($this->ranges())->map(function ($range, $key) {
                return ['label' => $range, 'value' => (string) $key];
            })->values()->all(),
        ]);
    }
}
