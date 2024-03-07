<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Fields;

use Syn\LaravelSwaggerJsonApiGenerator\Contracts\Field;
use Syn\LaravelSwaggerJsonApiGenerator\Contracts\FieldContract;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiDescriptionTrait;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiExampleTrait;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiTypeTrait;
use LaravelJsonApi\Eloquent\Fields\Boolean as BooleanField;

class Boolean extends BooleanField implements Field, FieldContract
{
    use OpenApiTypeTrait;
    use OpenApiDescriptionTrait;
    use OpenApiExampleTrait;

    public static function make(string $fieldName, string $column = null): self
    {
        return (new static($fieldName, $column))->typeUsing('boolean');
    }
}
