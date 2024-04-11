<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Traits;

trait OpenApiTypeTrait
{
    private string $type = 'string';
    private ?array $enum = null;

    public function typeUsing(string $type, ?array $enum = null): self
    {
        $this->type = $type;
        if ($this->type === 'string' && !is_null($enum)) {
            $this->enum = $enum;
        }

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getEnum(): ?array
    {
        return $this->enum;
    }
}
