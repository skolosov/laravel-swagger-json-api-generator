<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Fields;

use Syn\LaravelSwaggerJsonApiGenerator\Contracts\FieldContract;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiDescriptionTrait;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiExampleTrait;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiTypeTrait;
use LaravelJsonApi\Eloquent\Fields\Str as StrField;

class Str extends StrField implements FieldContract
{
    use OpenApiTypeTrait;
    use OpenApiDescriptionTrait;
    use OpenApiExampleTrait;

    public static function make(string $fieldName, string $column = null): self
    {
        return new static($fieldName, $column);
    }
}
