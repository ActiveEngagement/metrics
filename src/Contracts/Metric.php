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
     * @return \Actengage\Metrics\Contracts\Result;
     */
    public function calculate(Request $request): Result;

    /**
     * Instantiate a Result using the given value.
     *
     * @return \Actengage\Metrics\Contracts\Result;
     */
    public function result(mixed $value): Result;

    /**
     * Determine for how many minutes the metric should be cached.
     */
    public function cacheFor(): DateTimeInterface|DateInterval|float|int|null;

    /**
     * Set the `description` property.
     *
     * @return $this
     */
    public function description(?string $description): static;

    /**
     * Get the appropriate cache key for the metric.
     */
    public function getCacheKey(Request $request): string;

    /**
     * Get the displayable name of the metric.
     */
    public function name(): string;

    /**
     * Resolve the metric with the request.
     *
     * @return \Actengage\Metrics\Contracts\Result
     */
    public function resolve(Request $request): Result;

    /**
     * Set the `timezone` property.
     *
     * @return $this
     */
    public function timezone(?string $timezone): static;

    /**
     * Set the `title` property.
     *
     * @return $this
     */
    public function title(?string $title): static;

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string;
}
