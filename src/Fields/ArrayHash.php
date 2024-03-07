<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Fields;

use LaravelJsonApi\Eloquent\Fields\ArrayHash as ArrayHashField;
use Syn\LaravelSwaggerJsonApiGenerator\Contracts\Field;
use Syn\LaravelSwaggerJsonApiGenerator\Contracts\FieldObjectContract;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiObjectTrait;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiTypeTrait;

class ArrayHash extends ArrayHashField implements Field, FieldObjectContract
{
    use OpenApiTypeTrait;
    use OpenApiObjectTrait;
    public static function make(string $fieldName, string $column = null): self
    {
        return (new self($fieldName, $column))->typeUsing('object');
    }
}