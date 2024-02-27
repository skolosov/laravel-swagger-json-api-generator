<?php

namespace Skolosov\LaravelSwaggerJsonApiGenerator\Fields;

use Skolosov\LaravelSwaggerJsonApiGenerator\Contracts\FieldContract;
use Skolosov\LaravelSwaggerJsonApiGenerator\Traits\OpenApiDescriptionTrait;
use Skolosov\LaravelSwaggerJsonApiGenerator\Traits\OpenApiExampleTrait;
use Skolosov\LaravelSwaggerJsonApiGenerator\Traits\OpenApiTypeTrait;
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
