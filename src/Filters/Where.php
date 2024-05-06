<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Filters;


use Syn\LaravelSwaggerJsonApiGenerator\Contracts\FilterContract;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiDescriptionTrait;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiExampleTrait;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiTypeFilterTrait;
use LaravelJsonApi\Eloquent\Filters\Where as WhereFilter;

class Where extends WhereFilter implements FilterContract
{
    use OpenApiTypeFilterTrait;
    use OpenApiDescriptionTrait;
    use OpenApiExampleTrait;
}
