<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Filters;

use LaravelJsonApi\Eloquent\Filters\WhereIn as WhereInFilter;
use Syn\LaravelSwaggerJsonApiGenerator\Contracts\FilterContract;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiDescriptionTrait;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiExampleTrait;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiTypeFilterTrait;

class WhereIn extends WhereInFilter implements FilterContract
{
    use OpenApiTypeFilterTrait;
    use OpenApiDescriptionTrait;
    use OpenApiExampleTrait;
}