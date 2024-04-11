<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Contracts;

interface FilterContract
{
    public function typeUsing(string $type, ?array $enum = null): self;
    public function getType(): string;
    public function getEnum(): ?array;
    public function description(string $description): self;
    public function getDescription(): ?string;
    public function example(string $example): self;
    public function getExample(): ?string;
}
