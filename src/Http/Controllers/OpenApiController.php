<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

class OpenApiController extends Controller
{
    public function document(): string
    {
        return File::get(base_path('docs/v1/openapi.yaml'));
    }

    public function swagger(): View
    {
        return view('openapi::swagger.spec');
    }
}