<?php

namespace Skolosov\LaravelSwaggerJsonApiGenerator\Contracts;

interface FieldContract
{
    public function typeUsing(string $type): self;
    public function getType(): string;
    public function description(string $description): self;
    public function getDescription(): ?string;
    public function example(string $example): self;
    public function getExample(): ?string;
}
