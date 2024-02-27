<?php

namespace Skolosov\LaravelSwaggerJsonApiGenerator\Traits;

trait OpenApiExampleTrait
{
    private ?string $example = null;

    public function example(string $example): self
    {
        $this->example = $example;

        return $this;
    }

    public function getExample(): ?string
    {
        return $this->example;
    }

}
