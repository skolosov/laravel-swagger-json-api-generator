<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Fields\Relation;

use LaravelJsonApi\Eloquent\Fields\Relations\HasOneThrough as HasOneThroughRelation;
use Syn\LaravelSwaggerJsonApiGenerator\Contracts\Relation;
use Syn\LaravelSwaggerJsonApiGenerator\Contracts\RelationContract;
use Syn\LaravelSwaggerJsonApiGenerator\Contracts\RelationToOne;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiRelationTrait;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiTypeTrait;

class HasOneThrough extends HasOneThroughRelation implements Relation, RelationContract, RelationToOne
{
    use OpenApiTypeTrait;
    use OpenApiRelationTrait;
    public static function make(string $fieldName, string $relation = null): self
    {
        return new self($fieldName, $relation);
    }
}