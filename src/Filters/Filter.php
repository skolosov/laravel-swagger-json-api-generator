<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Filters;

use LaravelJsonApi\Eloquent\Contracts\Filter as BaseFilterContract;
use Syn\LaravelSwaggerJsonApiGenerator\Contracts\FilterContract;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiDescriptionTrait;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiExampleTrait;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiTypeFilterTrait;

abstract class Filter implements BaseFilterContract, FilterContract
{
    use OpenApiTypeFilterTrait;
    use OpenApiDescriptionTrait;
    use OpenApiExampleTrait;
}