<?php

if (!function_exists('package_path')) {
    function package_path(string $path = ''): string
    {
        return __DIR__ . "/../$path";
    }
}

if (!function_exists('docs_path')) {
    function docs_path(string $path = ''): string
    {
        return base_path("docs/$path");
    }
}

if (!function_exists('templates_path')) {
    function templates_path(string $path = ''): string
    {
        return __DIR__ . "/../templates/$path";
    }
}

if (!function_exists('openapi_base_path')) {
    function openapi_base_path(?string $path = null): string
    {
        $output_path = preg_replace("/(\/)$/", '', config('swagger-jsonapi-generator.output_path', 'v1'));
        return is_null($path)
            ? $output_path
            : "$output_path/" . preg_replace("/^(\/)/", '', config('swagger-jsonapi-generator.output_path', 'v1'));
    }
}
