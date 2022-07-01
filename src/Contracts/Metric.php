<?php

namespace Actengage\Metrics\Contracts;

use DateInterval;
use DateTimeInterface;
use Illuminate\Http\Request;
use JsonSerializable;

interface Metric extends JsonSerializable
{    
    /**
     * Calculate the results using the given request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Actengage\Metrics\Contracts\Result;
     */
    public function calculate(Request $request): Result;

    /**
     * Instantiate a Result using the given value.
     *
     * @param mixed $value
     * @return \Actengage\Metrics\Contracts\Result;
     */
    public function result(mixed $value): Result;

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return \DateTimeInterface|\DateInterval|float|int|null
     */
    public function cacheFor(): DateTimeInterface|DateInterval|float|int|null;

    /**
     * Set the `description` property.
     * 
     * @param string|null $description
     * @return $this
     */
    public function description(?string $description): static;

    /**
     * Get the appropriate cache key for the metric.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    public function getCacheKey(Request $request): string;

    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name(): string;

    /**
     * Resolve the metric with the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Actengage\Metrics\Contracts\Result
     */
    public function resolve(Request $request): Result;

    /**
     * Set the `timezone` property.
     *
     * @param string|null $timezone
     * @return $this
     */
    public function timezone(?string $timezone): static;

    /**
     * Set the `title` property.
     * 
     * @param string|null $title
     * @return $this
     */
    public function title(?string $title): static;

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string;
}