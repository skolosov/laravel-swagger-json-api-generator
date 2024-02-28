<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Services;

use Exception;

class OpenApiConfigService
{
    public array $resources;
    public string $server;
    public array $resourceNames;

    public function __construct()
    {
        [
            'resources' => $this->resources,
            'serverJsonApi' => $this->server,
            'resourceNames' => $this->resourceNames,
        ] = config('swagger-jsonapi-generator');
    }
}
