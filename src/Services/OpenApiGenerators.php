<?php

namespace Skolosov\LaravelSwaggerJsonApiGenerator\Services;


use Skolosov\LaravelSwaggerJsonApiGenerator\Contracts\FieldContract;
use Skolosov\LaravelSwaggerJsonApiGenerator\Contracts\FilterContract;
use Skolosov\LaravelSwaggerJsonApiGenerator\Contracts\RelationContract;
use Skolosov\LaravelSwaggerJsonApiGenerator\Contracts\RelationToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Relations\Relation;
use LaravelJsonApi\Eloquent\Schema;
use Symfony\Component\Yaml\Yaml;

class OpenApiGenerators
{
    private OpenApiSelections $selections;

    public function __construct()
    {
        $this->selections = new OpenApiSelections();
    }


    public function generateRequests(Schema $schema, $resourceEnum): void
    {
        $type = 'requestBodies';
        $template = $this->getYamlTemplate('requestBodies');
        $storeTemplate = $template['store'];
        $updateTemplate = $template['update'];
        $relationshipTemplate = $template['relationship'];

        $typeResource = $schema::model()::MODEL_TYPE;

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

        $this->generateYaml($templates, $type, $resourceEnum);
    }


    public function generateParameters(Schema $schema, $resourceEnum): void
    {
        $type = 'parameters';
        $resourceType = $schema::type();
        $template = $this->getYamlTemplate($type);
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
                    'type' => $filter->getType()
                ];
                return $result;
            }, []);

        $tmpInclude = $this->walkToArray($template['enum'], $includeFilterData);
        $tmpSort = $this->walkToArray($template['enum'], $sortFilterData);

        $tmpFilters = array_reduce($filtersNames, function ($result, $filter) use ($template) {
            $tmp = $this->walkToArray($template['query'], $filter);
            return array_merge($result, $tmp);
        }, []);

        $templates = [
            ...$tmpFilters,
            ...$tmpInclude,
            ...$tmpSort,
        ];

        $this->generateYaml($templates, $type, $resourceEnum);
    }


    public function generateSchemas(Schema $schema, $resourceEnum): void
    {
        $template = $this->getYamlTemplate('schemas');
        $fields = $schema->fields();

        $typeResource = $schema::model()::MODEL_TYPE;

        $attributesTemplateData = [
            'nameComponent' => "$typeResource.attributes",
            'properties' => []
        ];
        $requestAttributesTemplateData = [
            'nameComponent' => "$typeResource.request.attributes",
            'properties' => []
        ];
        foreach ($fields as $field) {
            /** @var FieldContract $field */
            if ($field instanceof RelationContract || $field instanceof ID) {
                continue;
            }
            $params['type'] = $field->getType();
            !is_null($field->getExample()) && $params['example'] = $field->getExample();
            !is_null($field->getDescription()) && $params['description'] = $field->getDescription();
            $attributesTemplateData['properties'][$field->name()] = $params;
            $field->isNotReadOnly(null) && $requestAttributesTemplateData['properties'][$field->name()] = $params;
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
                'type' => $field->getRelationshipModel()::MODEL_TYPE,
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
        $this->generateYaml($templates, 'schemas', $resourceEnum);
    }


    public function generateResponses(Schema $schema, $resourceEnum): void
    {
        $template = $this->getYamlTemplate('responses');
        $responseTemplate = $template['responseShow'];
        $responseIndexTemplate = $template['responseIndex'];
        $responseRelationshipTemplate = $template['responseRelationship'];

        $typeResource = $schema::model()::MODEL_TYPE;

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

        $this->generateYaml($templates, 'responses', $resourceEnum);
    }


    public function generatePath(Schema $schema, $resourceEnum): void
    {
        $routes = $this->getRoutes($schema::model());

        $template = $this->getYamlTemplate('paths');
        $urlTemplate = $template['urlTemplate'];
        $requestsTemplate = $template['requestsTemplate'];


        $data = [];
        foreach ($routes as $method => $routesArr) {
            foreach ($routesArr as $route) {
                ['isMany' => $isMany, 'modelType' => $modelType] = isset($route->defaults['resource_relationship'])
                    ? array_reduce((array)$schema->fields(), function ($result, $item) use ($route) {
                        if ($item instanceof RelationContract) {
                            if ($item->name() === $route->defaults['resource_relationship']) {
                                $type = $item->getRelationshipModel()::MODEL_TYPE;
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
                    'security' => $isSecurity ? [['bearerAuth' => [ ]]] : null,
                    'isMany' => $isMany,
                    'resource' => $modelType,
                    'method' => $method,
                    'tag' => $resourceEnum->name,
                    'action' => $action,
                    'summary' => match ($action) {
                        'index' => "Получение всех ресурсов $modelType",
                        'store' => "Создание ресурса $modelType",
                        'update' => "Изменение ресурса $modelType",
                        'destroy' => "Удаление ресурса $modelType",
                        'show' => "Получение ресурса $modelType",
                        'showRelated' => "Получение списка ресурсов $modelType связанных с ресурсом $resourceEnum->name",
                        'showRelationship' => "Получение списка идентификаторов ресурсов $modelType связанных с ресурсом $resourceEnum->name",
                        'updateRelationship' => "Изменение привязки ресурсов $modelType связанных с ресурсом $resourceEnum->name",
                        'attachRelationship' => "Привязка ресурсов $modelType связанных с ресурсом $resourceEnum->name",
                        'detachRelationship' => "Отвязать ресурсы $modelType связанных с ресурсом $resourceEnum->name",
                        default => $action,
                    }
                ];
            }
        }

        $data = Arr::sortDesc($data);

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

        $this->generateYaml($templates, 'paths', $resourceEnum);
    }

    public function generateMainOpenApiFile($enum): void
    {
        $template = $this->getYamlTemplate('main');

        $tagsTemplates = array_reduce(
            $enum::cases(), function ($result, $resourceEnum) {
            $result[] = [
                'name' => $resourceEnum->name,
                'description' => $resourceEnum->description()
            ];
            return $result;
        }, []);


        $componentsRefs = $this->getFullComponents();

        $params = [
            ...$componentsRefs,
            'tags' => $tagsTemplates,
        ];


        $mainTemplate = $this->walkToArray($template, $params);
        $openApiFile = Yaml::dump($mainTemplate, 2, 2);
        Storage::disk('docs')->put("v1/openapi.yaml", $openApiFile);
    }

    private function getYamlTemplate(string $type): array
    {
        return Yaml::parseFile(base_path("docs/templates/$type.yaml"));
    }


    private function generateYaml(array $openApiFile, string $componentType, $openApiResourceEnum): void
    {
        $openApiFile = Yaml::dump($openApiFile, 2, 2);

        $namespace = $openApiResourceEnum->namespace();

        Storage::disk('docs')
            ->put(
                "src/v1/components/$componentType/$namespace/$componentType.yaml",
                $openApiFile
            );
    }

    public function getRoutes(string $modelClass): array
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
                    $routeObj->defaults['resource_type'] === $modelClass::MODEL_TYPE
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
        $parametersFiles = Storage::disk('docs')->allFiles('src/v1/components/parameters');
        $requestBodiesFiles = Storage::disk('docs')->allFiles('src/v1/components/requestBodies');
        $responsesFiles = Storage::disk('docs')->allFiles('src/v1/components/responses');
        $schemasFiles = Storage::disk('docs')->allFiles('src/v1/components/schemas');
        $pathsFiles = Storage::disk('docs')->allFiles('src/v1/components/paths');
        $files = [
            'parameters' => $parametersFiles,
            'requestBodies' => $requestBodiesFiles,
            'responses' => $responsesFiles,
            'schemas' => $schemasFiles,
            'paths' => $pathsFiles,
        ];
        $data = [];
        foreach ($files as $type => $filesPaths) {
            $data[$type] = [];
            foreach ($filesPaths as $filePath) {
                if (Str::contains($filePath, '.gitignore')) {
                    continue;
                }
                $componentFileArray = Yaml::parseFile(base_path("docs/$filePath"));
                $data[$type] = array_merge(
                    $data[$type],
                    $componentFileArray ?? []
                );
            }
        }

        return $data;
    }
}
