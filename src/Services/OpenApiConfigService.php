<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Services;

use LaravelJsonApi\Core\Support\AppResolver;
use LaravelJsonApi\Eloquent\Schema;
use LaravelJsonApi\Core\Server\Server;

class OpenApiConfigService
{
    public array $resources;
    public string $server;
    public array $resourceNames;

    public Server $serverInstance;

    public function __construct()
    {
        [
            'resources' => $this->resources,
            'serverJsonApi' => $this->server,
            'resourceNames' => $this->resourceNames,
        ] = config('swagger-jsonapi-generator');
        $this->serverInstance = new ($this->server)(app(AppResolver::class), 'v1');
    }

    public function getSchemaInstance(string $schemaClass): Schema
    {
        return new ($schemaClass)($this->serverInstance);
    }
}
