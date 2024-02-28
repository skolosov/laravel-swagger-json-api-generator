<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Traits;

trait OpenApiExampleTrait
{
    private ?string $example = null;

    public function example(string|bool|int|null $example): self
    {
        $this->example = $example ?? '';

        return $this;
    }

    public function getExample(): ?string
    {
        return $this->example;
    }

}
