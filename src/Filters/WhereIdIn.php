<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Filters;

use Syn\LaravelSwaggerJsonApiGenerator\Contracts\FilterContract;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiDescriptionTrait;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiExampleTrait;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiTypeFilterTrait;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn as WhereIdInFilter;

class WhereIdIn extends WhereIdInFilter implements FilterContract
{
    use OpenApiTypeFilterTrait;
    use OpenApiDescriptionTrait;
    use OpenApiExampleTrait;
}
