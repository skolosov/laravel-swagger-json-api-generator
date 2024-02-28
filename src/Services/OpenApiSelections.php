<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Syn\LaravelSwaggerJsonApiGenerator\Enums\OpenApiComponentsEnum;
use Syn\LaravelSwaggerJsonApiGenerator\Models\SwaggerComponent;
use Symfony\Component\Yaml\Yaml;

class OpenApiSelections
{
    public function selectionRequestBodies(array $dataPath): array
    {
        /** @var Collection $components */
        $components = SwaggerComponent::query()
            ->where(
                SwaggerComponent::FIELD_TYPE,
                OpenApiComponentsEnum::REQUEST_BODIES->value
            )->get();

        /** @var array $componentsNames */
        $componentsNames = $components
            ->pluck(SwaggerComponent::FIELD_NAME)
            ->toArray();


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

                if (key_exists($requestBodyName, $componentsNames)) {
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
        /** @var Collection $components */
        $components = SwaggerComponent::query()
            ->where(
                SwaggerComponent::FIELD_TYPE,
                OpenApiComponentsEnum::RESPONSES->value
            )->get();

        /** @var array $componentsNames */
        $componentsNames = $components
            ->pluck(SwaggerComponent::FIELD_NAME)
            ->toArray();

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
                if (key_exists($responsesName, $componentsNames)) {
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
        /** @var Collection $components */
        $components = SwaggerComponent::query()
            ->where(
                SwaggerComponent::FIELD_TYPE,
                OpenApiComponentsEnum::PARAMETERS->value
            )->get();

        /** @var array $componentsNames */
        $componentsNames = $components
            ->pluck(SwaggerComponent::FIELD_NAME)
            ->toArray();

        foreach ($dataPath as &$requests) {
            foreach ($requests as &$request) {
                $modelType = $request['resource'];
                $parametersNames = match ($request['action']) {
                    'index' => [
                        "paginationPageNumber",
                        "paginationPageSize",
                        ...array_reduce(
                            $componentsNames,
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
                            $componentsNames,
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
                $request['parameters'] = array_reduce($parametersNames, function ($result, $param) use ($componentsNames){
                    if (key_exists($param, $componentsNames)) {
                        $result[] = ['$ref' => "#/components/parameters/$param"];
                    }
                    return $result;
                }, []);
            }
        }
        return $dataPath;
    }
}
