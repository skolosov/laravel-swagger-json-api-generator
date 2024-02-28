<?php


use Illuminate\Support\Facades\Route;
use Syn\LaravelSwaggerJsonApiGenerator\Http\Controllers\OpenApiController;


Route::prefix('docs')->group(function () {
    Route::get('spec/swagger', [OpenApiController::class, 'swagger'])->name('api.spec.swagger');
    Route::get('spec/document', [OpenApiController::class, 'document'])->name('api.spec.document');
});