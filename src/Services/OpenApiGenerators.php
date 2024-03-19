<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Services;


use Illuminate\Database\Eloquent\Collection;
use Syn\LaravelSwaggerJsonApiGenerator\Contracts\Field;
use Syn\LaravelSwaggerJsonApiGenerator\Contracts\FieldArrayContract;
use Syn\LaravelSwaggerJsonApiGenerator\Contracts\FieldContract;
use Syn\LaravelSwaggerJsonApiGenerator\Contracts\FieldObjectContract;
use Syn\LaravelSwaggerJsonApiGenerator\Contracts\FilterContract;
use Syn\LaravelSwaggerJsonApiGenerator\Contracts\Relation;
use Syn\LaravelSwaggerJsonApiGenerator\Contracts\RelationContract;
use Syn\LaravelSwaggerJsonApiGenerator\Contracts\RelationToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use LaravelJsonApi\Eloquent\Schema;
use Syn\LaravelSwaggerJsonApiGenerator\DTO\Resource;
use Syn\LaravelSwaggerJsonApiGenerator\Enums\OpenApiComponentsEnum;
use Syn\LaravelSwaggerJsonApiGenerator\Models\SwaggerComponent;
use Symfony\Component\Yaml\Yaml;

class OpenApiGenerators
{
    private OpenApiSelections $selections;
    private OpenApiConfigService $service;

    public function __construct()
    {
        $this->selections = new OpenApiSelections();
        $this->service = new OpenApiConfigService();
    }


    public function generateRequests(Schema $schema): void
    {
        $template = $this->getYamlTemplate(OpenApiComponentsEnum::REQUEST_BODIES->value);
        $storeTemplate = $template['store'];
        $updateTemplate = $template['update'];
        $relationshipTemplate = $template['relationship'];

        $typeResource = $schema::type();

        $templatesData = [
            'type' => $typeResource,
            'requestAttributes' => [
                '$ref' => "#/components/schemas/$typeResource.request.attributes"
            ],
            'requestRelationships' => [
                '$ref' => "#/components/schemas/$typeResource.request.relationships"
            ],
        ];


        $storeTmp = $this->walkToArray($storeTemplate, array_merge($templatesData, ['nameComponent' => "$typeResource.requestBody.store"]));
        $updateTmp = $this->walkToArray($updateTemplate, array_merge($templatesData, ['nameComponent' => "$typeResource.requestBody.update"]));
        $relationshipTmp = $this->walkToArray($relationshipTemplate, array_merge($templatesData, ['nameComponent' => "$typeResource.requestBody.relationship"]));

        $templates = [
            ...$storeTmp,
            ...$updateTmp,
            ...$relationshipTmp,
        ];

        $this->generateSwaggerComponents($templates, OpenApiComponentsEnum::REQUEST_BODIES->value);
    }

    public function generateParameters(Schema $schema): void
    {
        $resourceType = $schema::type();
        $template = $this->getYamlTemplate(OpenApiComponentsEnum::PARAMETERS->value);
        $fields = $schema->fields();

        $includeParams = array_reduce((array)$fields, function ($result, $field) {
            if ($field instanceof Relation) {
                $result[] = $field->name();
            }
            return $result;
        }, []);
        $includeFilterData = [
            'nameComponent' => "$resourceType.include",
            'key' => 'include',
            'enum' => $includeParams,
            'description' => 'Получение связанных ресурсов',
        ];

        $sortParams = array_reduce((array)$fields, function ($result, $field) {
            if (method_exists($field, 'isSortable') && $field->isSortable()) {
                $name = $field->name();
                $result[] = $name;
                $result[] = "-$name";
            }
            return $result;
        }, []);

        $sortFilterData = [
            'nameComponent' => "$resourceType.sort",
            'key' => 'sort',
            'enum' => $sortParams,
            'description' => 'Сортировка'
        ];

        $filters = $schema->filters();

        $filtersNames = array_reduce(
            (array)$filters,
            function ($result, $filter) use ($schema) {
                $nameComponent = $schema::type() . "." . $filter->key() . ".filter";
                /** @var FilterContract $filter */
                $result[] = [
                    'nameComponent' => $nameComponent,
                    'key' => $filter->key(),
                    'type' => $filter->getType(),
                    'description' => $filter->getDescription(),
                    'example' => $filter->getExample(),
                ];
                return $result;
            }, []);

        $tmpInclude = count($includeParams) ? $this->walkToArray($template['enum'], $includeFilterData) : [];
        $tmpSort = count($sortFilterData) ? $this->walkToArray($template['enum'], $sortFilterData) : [];

        $tmpFilters = array_reduce($filtersNames, function ($result, $filter) use ($template) {
            $tmp = $this->walkToArray($template['query'], $filter);
            return array_merge($result, $tmp);
        }, []);

        $templates = [
            ...$tmpFilters,
            ...$tmpInclude,
            ...$tmpSort,
        ];

        $this->generateSwaggerComponents($templates, OpenApiComponentsEnum::PARAMETERS->value);
    }


    public function generateSchemas(Schema $schema): void
    {
        $template = $this->getYamlTemplate(OpenApiComponentsEnum::SCHEMAS->value);
        $fields = $schema->fields();

        $typeResource = $schema::type();

        $attributesTemplateData = [
            'nameComponent' => "$typeResource.attributes",
            'properties' => []
        ];
        $requestAttributesTemplateData = [
            'nameComponent' => "$typeResource.request.attributes",
            'properties' => []
        ];
        foreach ($fields as $field) {
            $params = [];
            /** @var FieldContract $field */
            if (!$field instanceof Field) {
                continue;
            }
            if ($field instanceof FieldObjectContract) {
                $params['type'] = $field->getType();
                $params['properties'] = $field->getAccessors();
            } elseif ($field instanceof FieldArrayContract) {
                $params['type'] = 'array';
                $params['items'] = [
                    'type' => $field->getType(),
                    'properties' => $field->getAccessors(),
                ];
            } else {
                $params['type'] = $field->getType();
                !is_null($field->getExample()) && $params['example'] = $field->getExample();
                !is_null($field->getDescription()) && $params['description'] = $field->getDescription();
            }
            if (count($params)) {
                $attributesTemplateData['properties'][$field->name()] = $params;
                $field->isNotReadOnly(null) && $requestAttributesTemplateData['properties'][$field->name()] = $params;
            }
        }

        $relationshipsTemplateData = [
            'nameComponent' => "$typeResource.relationships",
            'relationships' => [],
        ];
        $requestRelationshipsTemplateData = [
            'nameComponent' => "$typeResource.request.relationships",
            'relationships' => [],
        ];
        foreach ($fields as $field) {
            /** @var RelationContract $field */
            if (!$field instanceof RelationContract) {
                continue;
            }
            $data = [
                'nameRelation' => $field->name(),
                'type' => $this->service->getSchemaInstance($field->getRelationshipSchema())::type(),
            ];
            $relationship = $this->walkToArray($template['relationship'], $data);
            $requestRelationship = $this->walkToArray($template['requestRelationship'], $data);

            $relationshipsTemplateData['relationships'] = array_merge($relationshipsTemplateData['relationships'], $relationship);
            $requestRelationshipsTemplateData['relationships'] = array_merge($requestRelationshipsTemplateData['relationships'], $requestRelationship);
        }

        $tmpAttributes = $this->walkToArray($template['attributes'], $attributesTemplateData);
        $tmpRequestAttributes = $this->walkToArray($template['requestAttributes'], $requestAttributesTemplateData);
        $tmpRelationships = $this->walkToArray($template['relationships'], $relationshipsTemplateData);
        $tmpRequestRelationships = $this->walkToArray($template['requestRelationships'], $requestRelationshipsTemplateData);

        $templates = [
            ...$tmpAttributes,
            ...$tmpRelationships,
            ...$tmpRequestAttributes,
            ...$tmpRequestRelationships,
        ];

        $this->generateSwaggerComponents($templates, OpenApiComponentsEnum::SCHEMAS->value);
    }


    public function generateResponses(Schema $schema): void
    {
        $template = $this->getYamlTemplate(OpenApiComponentsEnum::RESPONSES->value);
        $responseTemplate = $template['responseShow'];
        $responseIndexTemplate = $template['responseIndex'];
        $responseRelationshipTemplate = $template['responseRelationship'];

        $typeResource = $schema::type();

        $responseTemplateData = [
            'type' => $typeResource,
            'attributes' => [
                '$ref' => "#/components/schemas/$typeResource.attributes"
            ],
            'relationships' => [
                '$ref' => "#/components/schemas/$typeResource.relationships"
            ],
        ];
        $responseTmp = $this->walkToArray($responseTemplate, array_merge($responseTemplateData, ['nameComponent' => "$typeResource.response"]));
        $responseIndexTmp = $this->walkToArray($responseIndexTemplate, array_merge($responseTemplateData, ['nameComponent' => "$typeResource.response.index"]));
        $responseRelationshipTmp = $this->walkToArray($responseRelationshipTemplate, array_merge($responseTemplateData, ['nameComponent' => "$typeResource.response.relationship"]));

        $templates = [
            ...$responseTmp,
            ...$responseIndexTmp,
            ...$responseRelationshipTmp,
        ];

        $this->generateSwaggerComponents($templates, OpenApiComponentsEnum::RESPONSES->value);
    }


    public function generatePath(Schema $schema): void
    {
        $schemaModelType = $schema::type();
        $routes = $this->getRoutes($schema);

        $template = $this->getYamlTemplate(OpenApiComponentsEnum::PATHS->value);
        $urlTemplate = $template['urlTemplate'];
        $requestsTemplate = $template['requestsTemplate'];


        $data = [];
        foreach ($routes as $method => $routesArr) {
            foreach ($routesArr as $route) {
                ['isMany' => $isMany, 'modelType' => $modelType] = isset($route->defaults['resource_relationship'])
                    ? array_reduce((array)$schema->fields(), function ($result, $item) use ($route) {
                        if ($item instanceof RelationContract) {
                            if ($item->name() === $route->defaults['resource_relationship']) {
                                $type = $this->service->getSchemaInstance($item->getRelationshipSchema())::type();
                                $isMany = $item instanceof RelationToMany;
                                $result = ['isMany' => $isMany, 'modelType' => $type];
                            }
                        }
                        return $result;
                    })
                    : ['modelType' => $route->defaults['resource_type'], 'isMany' => null];
                $action = explode('@', $route->action['controller'])[1];
                $isSecurity = in_array('auth', $route->action['middleware']);
                $data[$route->uri][$method] = [
                    'security' => $isSecurity ? [['bearerAuth' => []]] : null,
                    'isMany' => $isMany,
                    'resource' => $modelType,
                    'method' => $method,
                    'tag' => $schemaModelType,
                    'action' => $action,
                    'summary' => match ($action) {
                        'index' => "Получение всех ресурсов $modelType",
                        'store' => "Создание ресурса $modelType",
                        'update' => "Изменение ресурса $modelType",
                        'destroy' => "Удаление ресурса $modelType",
                        'show' => "Получение ресурса $modelType",
                        'showRelated' => "Получение списка ресурсов $modelType связанных с ресурсом $schemaModelType",
                        'showRelationship' => "Получение списка идентификаторов ресурсов $modelType связанных с ресурсом $schemaModelType",
                        'updateRelationship' => "Изменение привязки ресурсов $modelType связанных с ресурсом $schemaModelType",
                        'attachRelationship' => "Привязка ресурсов $modelType связанных с ресурсом $schemaModelType",
                        'detachRelationship' => "Отвязать ресурсы $modelType связанных с ресурсом $schemaModelType",
                        default => $action,
                    }
                ];
            }
        }

        $data = Arr::sort($data);

        $data = $this->selections->selectionParameters($data);
        $data = $this->selections->selectionResponses($data);
        $data = $this->selections->selectionRequestBodies($data);

        $templates = [];
        foreach ($data as $uri => $requests) {
            $requestsTmp = [];
            foreach ($requests as $request) {
                $requestsTmp = array_merge($requestsTmp, $this->walkToArray($requestsTemplate, $request));
            }
            $fixUri = '/' . Str::of($uri)->replaceMatches("/{\w+}/", '{id}');
            $templates = array_merge($templates, $this->walkToArray($urlTemplate, ['uri' => $fixUri, 'requests' => $requestsTmp]));
        }

        $this->generateSwaggerComponents($templates, OpenApiComponentsEnum::PATHS->value);
    }

    private function generateSwaggerComponents(array $templates, string $type, bool $isBase = false): void
    {
        $data = [];
        foreach ($templates as $name => $component) {
            $data[] = [
                'type' => $type,
                'name' => $name,
                'component' => json_encode($component),
                'is_base' => $isBase,
            ];
        }

        SwaggerComponent::query()->insert($data);
    }

    public function generateMainOpenApiFile(array $resources = []): void
    {
        $template = $this->getYamlTemplate(OpenApiComponentsEnum::MAIN->value);

        $resources = Arr::map($resources, fn($value, $name) => new Resource(name: $name, value: $value));

        $tagsTemplates = array_reduce(
            $resources, function ($result, Resource $resource) {
            $result[] = [
                'name' => $resource->name,
                'description' => $resource->description()
            ];
            return $result;
        }, []);


        $componentsRefs = $this->getFullComponents();

        $params = [
            'title' => config('swagger-jsonapi-generator.title', 'Backend'),
            'version' => config('swagger-jsonapi-generator.version', '1.0.0'),
            'description' => config('swagger-jsonapi-generator.description', ''),
            'servers' => config('swagger-jsonapi-generator.servers', ['url' => 'http://localhost:8000/', 'description' => 'local']),
            'tags' => $tagsTemplates,
            ...$componentsRefs,
        ];


        $mainTemplate = $this->walkToArray($template, $params);
        $openApiFile = Yaml::dump($mainTemplate, 2, 2);
        $outputPath = "v1/openapi.yaml";
        Storage::disk('docs')->put($outputPath, $openApiFile);
    }

    public function loadBaseComponents(): void
    {
        $types = OpenApiComponentsEnum::cases();
        foreach ($types as $type) {
            if ($type === OpenApiComponentsEnum::MAIN) {
                continue;
            }
            $file = templates_path("baseComponents/$type->value.yaml");
            $templates = file_exists($file)
                ? Yaml::parseFile($file) ?? []
                : [];
            $templates = [
                ...$templates,
                ...$this->getComponents($type->value),
            ];
            $this->generateSwaggerComponents($templates, $type->value, true);
        }
    }

    private function getComponents(string $type): array
    {
        $componentsFiles = Storage::disk('docs')->allFiles("src/v1/components/$type");
        $data = [];
        foreach ($componentsFiles as $componentPath) {
            if (Str::contains($componentPath, ['.gitignore', '.gitkeep'])) {
                continue;
            }
            $componentFileArray = Yaml::parseFile(docs_path($componentPath));
            $data = [
                ...$data,
                ...$componentFileArray ?? [],
            ];
        }

        return $data;
    }

    private function getYamlTemplate(string $type): array
    {
        return Yaml::parseFile(templates_path("$type.yaml"));
    }

    public function getRoutes(Schema $schema): array
    {
        $routes = app(Route::class);
        $routes = $routes::getRoutes();
        $arrRoutes = [];


        foreach ($routes->getRoutesByMethod() as $method => $routesMethod) {
            if (in_array($method, ['HEAD', 'OPTION'])) {
                continue;
            }
            foreach ($routesMethod as $routeObj) {
                if (
                    isset($routeObj->defaults['resource_type']) &&
                    $routeObj->defaults['resource_type'] === $schema::type()
                ) {
                    $arrRoutes[strtolower($method)][] = $routeObj;
                }
            }
        }
        return $arrRoutes;
    }

    public function walkToArray(array $array, $params): array
    {
        $replaceVariables = function (string $string, array $params): array|string|null {
            // Используем регулярное выражение для поиска переменных вида {$varName}
            if (Str::of($string)->test("/[{]*\\$([a-zA-Z_]+)[}]*/")) {
                $matches = Str::of($string)->match("/[{]*\\$([a-zA-Z_]+)[}]*/");
                $varName = $matches->value();
                // Если переменная найдена в массиве $params, заменяем на значение
                if (array_key_exists($varName, $params)) {
                    if (is_array($params[$varName]) || is_null($params[$varName])) {
                        return $params[$varName];
                    }
                    return Str::replaceMatches("/[{]*\\$([a-zA-Z_]+)[}]*/", $params[$varName], $string);
                }
                return $string; // Возвращаем оригинальную переменную, если не найдена в $params
            }
            return $string;
        };

        foreach ($array as $key => &$item) {
            if (is_string($key)) {
                $newKey = $replaceVariables($key, $params);
                // Если ключ изменился, удаляем старый ключ и добавляем элемент с новым ключом
                if ($newKey !== $key) {
                    unset($array[$key]);
                    $key = $newKey;
                    $array[$newKey] = $item;
                }
            }
            // Заменяем переменные в значении элемента массива, если элемент является строкой
            if (is_string($item)) {
                $array[$key] = $replaceVariables($item, $params);
                if (is_null($array[$key])) {
                    unset($array[$key]);
                }
            }

            if (is_array($item)) {
                $array[$key] = $this->walkToArray($item, $params);
            }
        }

        return $array;
    }

    private function getFullComponents(): array
    {
        /** @var Collection $components */
        $components = SwaggerComponent::query()
            ->orderBy(SwaggerComponent::FIELD_TYPE)
            ->orderByRaw('length(name) ASC')
            ->orderBy(SwaggerComponent::FIELD_NAME)
            ->get();

        $data = [];
        foreach ($components as $component) {
            /** @var SwaggerComponent $component */
            $data[$component->type][$component->name] = $component->component;
        }

        return $data;
    }
}
