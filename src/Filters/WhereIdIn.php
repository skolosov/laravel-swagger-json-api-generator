<?php

namespace Skolosov\LaravelSwaggerJsonApiGenerator\Filters;

use Skolosov\LaravelSwaggerJsonApiGenerator\Contracts\FilterContract;
use Skolosov\LaravelSwaggerJsonApiGenerator\Traits\OpenApiDescriptionTrait;
use Skolosov\LaravelSwaggerJsonApiGenerator\Traits\OpenApiExampleTrait;
use Skolosov\LaravelSwaggerJsonApiGenerator\Traits\OpenApiTypeTrait;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn as WhereIdInFilter;

class WhereIdIn extends WhereIdInFilter implements FilterContract
{
    use OpenApiTypeTrait;
    use OpenApiDescriptionTrait;
    use OpenApiExampleTrait;
}
