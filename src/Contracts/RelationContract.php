<?php

namespace Skolosov\LaravelSwaggerJsonApiGenerator\Contracts;

interface RelationContract
{
    public function relationshipModel(string $modelClass): self;
    public function getRelationshipModel(): string;
    public function typeUsing(string $type): self;
    public function getType(): string;

}
