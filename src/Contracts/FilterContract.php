<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Contracts;

interface FilterContract
{
    public function typeUsing(string $type, ?array $enum = null): self;

    public function typeCustom(string $componentName, string $templateComponentName, array $args = []): self;

    public function typeCustomSplit(string $templateName, array $componentNamesWithArgs): self;

    public function getType(): string;

    public function getTemplateComponentName(): ?string;

    public function getComponentName(): null|string|array;

    public function getArgs(): array;

    public function getEnum(): ?array;

    public function isComponent(): bool;

    public function isSplitComponent(): bool;

    public function description(string $description): self;

    public function getDescription(): ?string;

    public function example(string $example): self;

    public function getExample(): ?string;
}
