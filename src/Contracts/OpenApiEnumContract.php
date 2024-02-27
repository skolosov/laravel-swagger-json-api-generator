<?php

namespace Skolosov\LaravelSwaggerJsonApiGenerator\Contracts;

interface OpenApiEnumContract
{
    public function isUntrackedCase(): bool;
    public function getServerClass(): string;
    public function namespace(): string;
    public function description(): string;

}