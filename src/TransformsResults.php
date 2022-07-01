<?php

namespace Actengage\Metrics;

use Closure;

trait TransformsResults
{
    /**
     * The callback used to transform the value before display.
     *
     * @var \Closure|null
     */
    public Closure|null $transformCallback = null;

    /**
     * Set the callback used to transform the value before presentation.
     *
     * @param  \Closure|null  $transformCallback
     * @return $this
     */
    public function transform(Closure|null $transformCallback)
    {
        $this->transformCallback = $transformCallback;

        return $this;
    }

    /**
     * Resolve the transformed value result.
     *
     * @param  mixed  $value
     * @return mixed
     */
    public function resolveTransformedValue($value)
    {
        return transform($value, $this->transformCallback ?? function ($value) {
            return $value;
        });
    }
}
