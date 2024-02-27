<?php

namespace Skolosov\LaravelSwaggerJsonApiGenerator\Fields\Relation;


use Skolosov\LaravelSwaggerJsonApiGenerator\Contracts\RelationContract;
use Skolosov\LaravelSwaggerJsonApiGenerator\Contracts\RelationToMany;
use Skolosov\LaravelSwaggerJsonApiGenerator\Traits\OpenApiRelationTrait;
use Skolosov\LaravelSwaggerJsonApiGenerator\Traits\OpenApiTypeTrait;
use LaravelJsonApi\Eloquent\Fields\Relations\HasMany as HasManyRelation;

class HasMany extends HasManyRelation implements RelationContract, RelationToMany
{
    use OpenApiTypeTrait;
    use OpenApiRelationTrait;

    public static function make(string $fieldName, string $relation = null): self
    {
        return new self($fieldName, $relation);
    }
}
