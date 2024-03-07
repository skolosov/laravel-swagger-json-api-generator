<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Traits;

trait OpenApiObjectTrait
{
    public array $accessors = [];

    public function accessor(
        string $field,
        string $type = 'string',
        mixed $example = null,
        ?string $description = null,
    ): self
    {

        $this->accessors[$field] = [
            'type' => $type,
            ...!is_null($example) ? ['example' => $example] : [],
            ...!is_null($description) ? ['description' => $description] : [],
        ];

        return $this;
    }

    public function getAccessors(): array
    {
        return $this->accessors;
    }
}