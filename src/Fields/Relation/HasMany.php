<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Fields\Relation;


use Syn\LaravelSwaggerJsonApiGenerator\Contracts\Relation;
use Syn\LaravelSwaggerJsonApiGenerator\Contracts\RelationContract;
use Syn\LaravelSwaggerJsonApiGenerator\Contracts\RelationToMany;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiRelationTrait;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiTypeTrait;
use LaravelJsonApi\Eloquent\Fields\Relations\HasMany as HasManyRelation;

class HasMany extends HasManyRelation implements Relation, RelationContract, RelationToMany
{
    use OpenApiTypeTrait;
    use OpenApiRelationTrait;

    public static function make(string $fieldName, string $relation = null): self
    {
        return new self($fieldName, $relation);
    }
}
