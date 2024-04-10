<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Filters;


use Syn\LaravelSwaggerJsonApiGenerator\Contracts\FilterContract;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiDescriptionTrait;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiExampleTrait;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiTypeTrait;
use LaravelJsonApi\Eloquent\Filters\WhereNotNull as WhereFilter;

class WhereNotNull extends WhereFilter implements FilterContract
{
    use OpenApiTypeTrait;
    use OpenApiDescriptionTrait;
    use OpenApiExampleTrait;
}
