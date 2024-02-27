<?php

namespace Skolosov\LaravelSwaggerJsonApiGenerator\Fields;

use Skolosov\LaravelSwaggerJsonApiGenerator\Contracts\FieldContract;
use Skolosov\LaravelSwaggerJsonApiGenerator\Traits\OpenApiDescriptionTrait;
use Skolosov\LaravelSwaggerJsonApiGenerator\Traits\OpenApiExampleTrait;
use Skolosov\LaravelSwaggerJsonApiGenerator\Traits\OpenApiTypeTrait;
use LaravelJsonApi\Eloquent\Fields\ID as IDField;

class ID extends IDField implements FieldContract
{
    use OpenApiTypeTrait;
    use OpenApiDescriptionTrait;
    use OpenApiExampleTrait;
}
