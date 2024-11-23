<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Traits;

trait OpenApiRelationTrait
{
    private ?string $relationshipModel = null;

    public function relationshipSchema(string $modelClass): self
    {
        $this->relationshipModel = $modelClass;

        return $this;
    }

    public function getRelationshipSchema(): string
    {
        return $this->relationshipModel;
    }
}
