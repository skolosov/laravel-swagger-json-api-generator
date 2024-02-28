<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Traits;

trait OpenApiDescriptionTrait
{
    private ?string $description = null;

    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
