<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

class OpenApiController extends Controller
{
    public function document(): string
    {
        $extension = config('swagger-jsonapi-generator.output_format', 'yaml');
        $path = openapi_base_path("openapi.$extension");
        return File::get(docs_path("$path"));
    }

    public function swagger(): View
    {
        return view('openapi::swagger.spec');
    }
}