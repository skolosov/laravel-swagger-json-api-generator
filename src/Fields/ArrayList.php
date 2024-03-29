<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Fields;


use Syn\LaravelSwaggerJsonApiGenerator\Contracts\Field;
use Syn\LaravelSwaggerJsonApiGenerator\Contracts\FieldArrayContract;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiObjectTrait;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiTypeTrait;
use LaravelJsonApi\Eloquent\Fields\ArrayList as ArrayListField;

class ArrayList extends ArrayListField implements Field, FieldArrayContract
{
    use OpenApiTypeTrait;
    use OpenApiObjectTrait;

    public static function make(string $fieldName, string $column = null): self
    {
        return (new self($fieldName, $column))->typeUsing('object');
    }
}
