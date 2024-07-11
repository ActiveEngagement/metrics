<?php

namespace Actengage\Metrics\Results;

use Actengage\Metrics\Contracts\Result;
use Actengage\Metrics\TransformsResults;

class TrendResult implements Result
{
    use TransformsResults;

    /**
     * The value of the result.
     *
     * @var int|float|numeric-string|null
     */
    public $value;

    /**
     * The metric value prefix.
     */
    public ?string $prefix = null;

    /**
     * The previous value.
     */
    public array $trend = [];

    /**
     * The metric value suffix.
     */
    public ?string $suffix = null;

    /**
     * Determines whether a value of 0 counts as "No Current Data".
     */
    public bool $zeroResult = false;

    /**
     * Create a new trend result instance.
     *
     * @param  int|float|numeric-string|null  $value
     * @return void
     */
    public function __construct($value = null)
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
     * Set the trend of data for the metric.
     *
     * @param  array<string, int|float|numeric-string|null>  $trend
     * @return $this
     */
    public function trend(array $trend)
    {
        $this->trend = $trend;

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
     * Prepare the metric result for JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'value' => $this->resolveTransformedValue($this->value),
            'trend' => collect($this->trend)->transform(function ($value) {
                return $this->resolveTransformedValue($value);
            })->all(),
            'prefix' => $this->prefix,
            'suffix' => $this->suffix
        ];
    }
}
