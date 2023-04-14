<?php

namespace Actengage\Metrics;

trait DescribeResults
{
    /**
     * A description of the results.
     */
    public ?string $description = null;

    /**
     * A title for the results.
     *
     * @var string|string
     */
    public ?string $title = null;

    /**
     * Set the `description` property.
     *
     * @return $this
     */
    public function description(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set the `title` property.
     *
     * @return $this
     */
    public function title(?string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
