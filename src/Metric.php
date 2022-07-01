<?php

namespace Actengage\Metrics;

use Actengage\Metrics\Contracts\Metric as MetricInterface;
use Actengage\Metrics\Contracts\Result;
use DateInterval;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use JsonSerializable;

abstract class Metric implements MetricInterface, JsonSerializable
{
    use DescribeResults;

    /**
     * The displayable name of the metric.
     *
     * @var string|null
     */
    public ?string $name = null;

    /**
     * A date range for the query.
     *
     * @var \Actengage\Metrics\DateRange|null
     */
    public ?DateRange $range = null;

    /**
     * The timezone that is to be used dates.
     *
     * @var string|null
     */
    public ?string $timezone = null;

    /**
     * Calculate the results using the given request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Actengage\Metrics\Contracts\Result;
     */
    abstract function calculate(Request $request): Result;

    /**
     * Instantiate a Result using the given value.
     *
     * @param mixed $value
     * @return \Actengage\Metrics\Contracts\Result;
     */
    abstract function result(mixed $value): Result;

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return \DateTimeInterface|\DateInterval|float|int|null
     */
    public function cacheFor(): DateTimeInterface|DateInterval|float|int|null
    {
        return null;
    }

    /**
     * Get the appropriate cache key for the metric.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    public function getCacheKey(Request $request): string
    {
        return sprintf(
            'actengage.metric.%s.%s',
            $this->uriKey(),
            $this->range
        );
    }

    /**
     * Get thed default timezone.
     *
     * @return string
     */
    public function getDefaultTimezone(): string
    {
        return $this->timezone ?? config('app.timezone');
    }

    /**
     * Prepare the metric for JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name(),
            'uri_key' => $this->uriKey(),
            'title' => $this->title,
            'description' => $this->description,
        ];
    }

    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name ?: str(class_basename(get_class($this)))->title()->snake(' ')->toString();
    }

    // /**
    //  * Set the `range` property.
    //  *
    //  * @param \Actengage\Metrics\DateRange|string|null $range
    //  * @return $this
    //  */
    // public function range(DateRange|string|null $range): static
    // {
    //     if(is_string($range)) {
    //         $range = DateRange::$range();
    //     }

    //     $this->range = $range;

    //     return $this;
    // }

    /**
     * Resolve the metric with the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Actengage\Metrics\Contracts\Result
     */
    public function resolve(Request $request): Result
    {
        $resolver = function () use ($request) {
            return $this->calculate($request);
        };

        if($cacheFor = $this->cacheFor()) {
            $cacheFor = is_numeric($cacheFor) ? new DateInterval(sprintf('PT%dS', $cacheFor * 60)) : $cacheFor;

            return Cache::remember(
                $this->getCacheKey($request),
                $cacheFor,
                $resolver
            );
        }

        return $resolver();
    }

    /**
     * Set the `timezone` property.
     *
     * @param string|null $timezone
     * @return $this
     */
    public function timezone(?string $timezone): static
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return str()->slug($this->name(), '-', null);
    }
}