<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Contracts;

interface FieldArrayContract
{
    public function typeUsing(string $type, ?array $enum = null): self;
    public function getType(): string;

    public function accessor(
        string $field,
        string $type = 'string',
        mixed $example = null,
        ?string $description = null,
    ): self;

    public function getAccessors(): array;
}