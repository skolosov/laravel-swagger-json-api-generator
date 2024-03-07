<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Contracts;

interface FieldObjectContract
{
    public function typeUsing(string $type): self;
    public function getType(): string;

    public function accessor(
        string $field,
        string $type = 'string',
        mixed $example = null,
        ?string $description = null,
    ): self;

    public function getAccessors(): array;
}