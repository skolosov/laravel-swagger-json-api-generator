<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Fields\Relation;


use Syn\LaravelSwaggerJsonApiGenerator\Contracts\Relation;
use Syn\LaravelSwaggerJsonApiGenerator\Contracts\RelationContract;
use Syn\LaravelSwaggerJsonApiGenerator\Contracts\RelationToOne;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiRelationTrait;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiTypeTrait;
use LaravelJsonApi\Eloquent\Fields\Relations\HasOne as HasOneRelation;

class HasOne extends HasOneRelation implements Relation, RelationContract, RelationToOne
{
    use OpenApiTypeTrait;
    use OpenApiRelationTrait;

    public static function make(string $fieldName, string $relation = null): self
    {
        return new self($fieldName, $relation);
    }
}
