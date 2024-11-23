<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Contracts;

interface RelationContract
{
    public function relationshipSchema(string $modelClass): self;
    public function getRelationshipSchema(): string;
    public function typeUsing(string $type, ?array $enum = null): self;
    public function getType(): string;

}
