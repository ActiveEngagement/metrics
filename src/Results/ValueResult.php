<?php

namespace Actengage\Metrics\Results;

use Actengage\Metrics\Contracts\Result;
use Actengage\Metrics\TransformsResults;

class ValueResult implements Result
{
    use TransformsResults;

    /**
     * The value of the result.
     */
    public mixed $value;

    /**
     * The metric value prefix.
     */
    public ?string $prefix = null;

    /**
     * The previous value.
     */
    public mixed $previous = null;

    /**
     * The metric value suffix.
     */
    public ?string $suffix = null;

    /**
     * Determines whether a value of 0 counts as "No Current Data".
     */
    public bool $zeroResult = false;

    /**
     * Create a new value result instance.
     *
     * @param  int|float|numeric-string|null  $value
     * @return void
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Indicate that the metric represents a currency value.
     *
     * @param  string  $symbol
     * @return $this
     */
    public function currency($symbol = '$'): static
    {
        return $this->prefix($symbol);
    }

    /**
     * Indicate that the metric represents a dollar value.
     *
     * @param  string  $symbol
     * @return $this
     */
    public function dollars($symbol = '$'): static
    {
        return $this->currency($symbol);
    }

    /**
     * Calculate the percentage of chang over the last cycle.
     *
     * @return mixed
     */
    public function percentChanged()
    {
        if ($this->value == 0) {
            $value = $this->previous;
        } elseif ($this->previous == 0) {
            $value = $this->value;
        } else {
            $value = ($this->previous - $this->value) / $this->previous;
        }

        return round(abs($value) * 100, 2) * ($this->value >= $this->previous ? 1 : -1);
    }

    /**
     * Set the metric value prefix.
     *
     * @param  string  $prefix
     * @return $this
     */
    public function prefix($prefix): static
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Set the previous value for the metric.
     *
     * @param  mixed  $previous
     * @return $this
     */
    public function previous($previous): static
    {
        $this->previous = $previous;
        // $this->previousLabel = $label;

        return $this;
    }

    /**
     * Set the metric value suffix.
     *
     * @param  string  $suffix
     * @return $this
     */
    public function suffix($suffix): static
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * Sets the `zeroResult` property.
     *
     * @return $this
     */
    public function allowZeroResult(bool $zeroResult = true): static
    {
        $this->zeroResult = $zeroResult;

        return $this;
    }

    /**
     * Prepare the metric result for JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'value' => $this->resolveTransformedValue($this->value),
            'previous' => $this->resolveTransformedValue($this->previous),
            'percent_changed' => $percentChanged = $this->percentChanged(),
            'positive_change' => $percentChanged >= 0,
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
            'zero_result' => $this->zeroResult,
        ];
    }
}
