<?php

namespace Skolosov\LaravelSwaggerJsonApiGenerator\Fields\Relation;

use Skolosov\LaravelSwaggerJsonApiGenerator\Contracts\RelationContract;
use Skolosov\LaravelSwaggerJsonApiGenerator\Contracts\RelationToOne;
use Skolosov\LaravelSwaggerJsonApiGenerator\Traits\OpenApiRelationTrait;
use Skolosov\LaravelSwaggerJsonApiGenerator\Traits\OpenApiTypeTrait;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsTo as BelongsToRelation;

class BelongsTo extends BelongsToRelation implements RelationContract, RelationToOne
{
    use OpenApiTypeTrait;
    use OpenApiRelationTrait;

    public static function make(string $fieldName, string $relation = null): self
    {
        return new static($fieldName, $relation);
    }
}
