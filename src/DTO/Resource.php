<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\DTO;

use Illuminate\Support\Arr;

class Resource
{
    public string $name;
    public string $value;

    public function __construct(string $name, string $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function description()
    {
        return Arr::first(config('swagger-jsonapi-generator.resourceNames'), fn($value, $key) => $this->name === $key, '');
    }
}