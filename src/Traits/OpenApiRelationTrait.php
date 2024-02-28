<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Traits;

trait OpenApiRelationTrait
{
    private ?string $relationshipModel = null;

    public function relationshipModel(string $modelClass): self
    {
        $this->relationshipModel = $modelClass;

        return $this;
    }

    public function getRelationshipModel(): string
    {
        return $this->relationshipModel;
    }
}
