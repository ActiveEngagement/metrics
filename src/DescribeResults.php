<?php

namespace Actengage\Metrics;

trait DescribeResults
{
    /**
     * A description of the results.
     *
     * @var string|null
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
     * @param string|null $description
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
     * @param string|null $title
     * @return $this
     */
    public function title(?string $title): static
    {
        $this->title = $title;

        return $this;
    }
}