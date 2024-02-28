<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Enums;

enum OpenApiComponentsEnum: string
{
    case MAIN = 'main';
    case PARAMETERS = 'parameters';
    case PATHS = 'paths';
    case REQUEST_BODIES = 'requestBodies';
    case RESPONSES = 'responses';
    case SCHEMAS = 'schemas';
}
