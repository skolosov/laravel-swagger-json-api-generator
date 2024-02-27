<?php

namespace Skolosov\LaravelSwaggerJsonApiGenerator\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

class OpenApiSelections
{
    public function selectionRequestBodies(array $dataPath): array
    {
        $requestBodiesComponents = $this->getComponents('requestBodies');
        foreach ($dataPath as &$requests) {
            foreach ($requests as &$request) {
                $modelType = $request['resource'];
                $requestBodyName = match ($request['action']) {
                    'store' => "$modelType.requestBody.store",
                    'update' => "$modelType.requestBody.update",
                    'updateRelationship',
                    'attachRelationship',
                    'detachRelationship' => "$modelType.requestBody.relationship",
                    default => null,
                };

                if (key_exists($requestBodyName, $requestBodiesComponents)) {
                    $request['requestBody'] = ['$ref' => "#/components/requestBodies/$requestBodyName"];
                } else {
                    $request['requestBody'] = null;
                }
            }
        }

        return $dataPath;
    }

    public function selectionResponses(array $dataPath): array
    {
        $responsesComponents = $this->getComponents('responses');
        foreach ($dataPath as &$requests) {
            foreach ($requests as &$request) {
                $modelType = $request['resource'];
                $responsesName = match ($request['action']) {
                    'index', 'showRelated' => "$modelType.response.index",
                    'store', 'update', 'show' => "$modelType.response",
                    'showRelationship',
                    'updateRelationship',
                    'attachRelationship',
                    'detachRelationship' => "$modelType.response.relationship",
                    default => 'noContent'
                };
                if (key_exists($responsesName, $responsesComponents)) {
                    $request['responses'] = [
                        200 => ['$ref' => "#/components/responses/$responsesName"]
                    ];
                } else {
                    $request['responses'] = [
                        200 => [
                            'description' => 'noContent',
                            'content' => [
                                'application/vnd.api+json' => [
                                    'schema' => [
                                        'type' => 'object'
                                    ]
                                ]
                            ],
                        ]
                    ];
                }
            }
        }

        return $dataPath;
    }

    public function selectionParameters(array $dataPath): array
    {
        $parametersComponents = $this->getComponents('parameters');
        foreach ($dataPath as &$requests) {
            foreach ($requests as &$request) {
                $modelType = $request['resource'];
                $parametersNames = match ($request['action']) {
                    'index' => [
                        "paginationPageNumber",
                        "paginationPageSize",
                        ...array_reduce(
                            array_keys($parametersComponents),
                            function ($result, $filter) use ($modelType) {
                                if (Str::substrCount($filter, $modelType)) {
                                    $result[] = $filter;
                                }
                                return $result;
                            }, [])
                    ],
                    'showRelated' => [
                        "baseParameterId",
                        ...$request['isMany'] ? array_reduce(
                            array_keys($parametersComponents),
                            function ($result, $filter) use ($modelType) {
                                if (Str::substrCount($filter, $modelType) &&
                                    !Str::contains($filter, ['include'])
                                ) {
                                    $result[] = $filter;
                                }
                                return $result;
                            }, []) : [],
                        ...$request['isMany']
                            ? ["paginationPageNumber", "paginationPageSize"]
                            : []
                    ],
                    'store' => [
                        "{$modelType}.include",
                    ],
                    'update', 'show' => [
                        "baseParameterId",
                        "{$modelType}.include",
                    ],
                    'destroy',
                    'showRelationship',
                    'updateRelationship',
                    'attachRelationship',
                    'detachRelationship' => [
                        "baseParameterId",
                    ],
                    default => []
                };
                $request['parameters'] = array_reduce($parametersNames, function ($result, $param) use ($parametersComponents) {
                    if (key_exists($param, $parametersComponents)) {
                        $result[] = ['$ref' => "#/components/parameters/$param"];
                    }
                    return $result;
                }, []);
            }
        }
        return $dataPath;
    }

    public function getComponents(string $type): array
    {
        $componentsFiles = Storage::disk('docs')->allFiles("src/v1/components/$type");
        $data = [];
        foreach ($componentsFiles as $componentPath) {
            if (Str::contains($componentPath, '.gitignore')) {
                continue;
            }
            $componentFileArray = Yaml::parseFile(base_path("docs/$componentPath"));
            $data = array_merge(
                $data,
                $componentFileArray ?? []
            );
        }

        return $data;
    }
}
