<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Traits;

trait OpenApiTypeFilterTrait
{
    private string $type = 'string';
    private ?array $customType = null;
    private ?array $splitCustomType = null;
    private ?array $enum = null;
    private bool $split = false;

    public function typeUsing(string $type, ?array $enum = null): self
    {
        $this->split = false;
        $this->customType = null;
        $this->splitCustomType = null;
        $this->enum = null;

        $this->type = $type;
        if ($this->type === 'string' && !is_null($enum)) {
            $this->enum = $enum;
        }

        return $this;
    }

    public function typeCustom(string $componentName, string $templateComponentName, array $args = []): self
    {
        $this->split = false;
        $this->type = 'component';
        $this->splitCustomType = null;
        $this->enum = null;

        $this->customType = ['template' => $templateComponentName, 'componentName' => $componentName, 'args' => $args];

        return $this;
    }

    public function typeCustomSplit(string $templateName, array $componentNamesWithArgs): self
    {
        $this->split = true;
        $this->type = 'component';
        $this->customType = null;

        $data = [];
        foreach ($componentNamesWithArgs as $componentName => $args) {
            $data[] = ['componentName' => $componentName, 'args' => $args];
        }
        $this->splitCustomType = ['template' => $templateName, 'data' => $data];

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTemplateComponentName(): ?string
    {
        return $this->split
            ? data_get($this->splitCustomType, 'template')
            : data_get($this->customType, 'template');
    }

    public function getComponentName(): null|string|array
    {
        return $this->split
            ? data_get($this->splitCustomType , 'data.*.componentName')
            : data_get($this->customType, 'componentName');
    }

    public function getArgs(): array
    {
        return $this->split
            ? data_get($this->splitCustomType, 'data.*.args', [])
            : data_get($this->customType, 'args', []);
    }

    public function getEnum(): ?array
    {
        return $this->enum;
    }

    public function isComponent(): bool
    {
        return $this->type === 'component';
    }

    public function isSplitComponent(): bool
    {
        return $this->split;
    }
}
