<?php

namespace Actengage\Metrics\Results;

use Actengage\Metrics\RangedResult;
use Actengage\Metrics\TransformsResults;

class TrendResult extends RangedResult
{
    use TransformsResults;

    // /**
    //  * The metric value formatting.
    //  *
    //  * @var string|null
    //  */
    // public ?string $format = null;

    /**
     * The metric value prefix.
     *
     * @var string|null
     */
    public ?string $prefix = null;

    /**
     * The previous value.
     *
     * @var array
     */
    public array $trend = [];

    // /**
    //  * The previous value label.
    //  *
    //  * @var string
    //  */
    // public ?string $previousLabel = null;

    /**
     * The metric value suffix.
     *
     * @var string|null
     */
    public ?string $suffix = null;

    /**
     * Determines whether a value of 0 counts as "No Current Data".
     *
     * @var boolean
     */
    public bool $zeroResult = false;

    // /**
    //  * Set the metric value formatting.
    //  *
    //  * @param string $format
    //  * @return $this
    //  */
    // public function format($format): static
    // {
    //     $this->format = $format;

    //     return $this;
    // }

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
     * @param string $prefix
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
     * @param string $suffix
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
        return array_merge([
            'value' => $this->resolveTransformedValue($this->value),
            'trend' => collect($this->trend)->transform(function ($value) {
                return $this->resolveTransformedValue($value);
            })->all(),
            // 'previousLabel' => $this->previousLabel,
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
            // 'suffixInflection' => $this->suffixInflection,
            // 'format' => $this->format,
        ], parent::jsonSerialize());
    }
}