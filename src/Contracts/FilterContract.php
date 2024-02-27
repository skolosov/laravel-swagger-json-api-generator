<?php

namespace Skolosov\LaravelSwaggerJsonApiGenerator\Contracts;

interface FilterContract
{
    public function typeUsing(string $type): self;
    public function getType(): string;
    public function description(string $description): self;
    public function getDescription(): ?string;
    public function example(string $example): self;
    public function getExample(): ?string;
}
