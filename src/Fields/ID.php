<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Fields;

use Syn\LaravelSwaggerJsonApiGenerator\Contracts\FieldContract;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiDescriptionTrait;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiExampleTrait;
use Syn\LaravelSwaggerJsonApiGenerator\Traits\OpenApiTypeTrait;
use LaravelJsonApi\Eloquent\Fields\ID as IDField;

class ID extends IDField implements FieldContract
{
    use OpenApiTypeTrait;
    use OpenApiDescriptionTrait;
    use OpenApiExampleTrait;
}
