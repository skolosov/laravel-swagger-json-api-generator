<?php

namespace Skolosov\LaravelSwaggerJsonApiGenerator\Traits;

trait OpenApiTypeTrait
{
    private string $type = 'string';

    public function typeUsing(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
