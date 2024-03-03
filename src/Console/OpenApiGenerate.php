<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Console;


use Syn\LaravelSwaggerJsonApiGenerator\Models\SwaggerComponent;
use Syn\LaravelSwaggerJsonApiGenerator\Services\OpenApiConfigService;
use Syn\LaravelSwaggerJsonApiGenerator\Services\OpenApiGenerators;
use Exception;
use Illuminate\Console\Command;
use LaravelJsonApi\Core\Support\AppResolver;

class OpenApiGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docs:gen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Генерация Документации OpenApi';

    /**
     * Execute the console command.
     * @throws Exception
     */
    public function handle(OpenApiConfigService $service, OpenApiGenerators $generators): void
    {
        SwaggerComponent::query()->truncate();

        $tags = $service->resources;

        $bar = $this->output->createProgressBar(count($tags) + 1);
        $bar->setFormat("%current%/%max% [%bar%] %percent:1s%% %message%\n");
        $bar->start();
        $bar->setMessage("Генерация базовых компонентов");
        $generators->loadBaseComponents();
        foreach ($tags as $tag => $schema) {
            if (!class_exists($schema)) {
                continue;
            }

            $bar->setMessage("Загрзка $tag");
            $server = new ($service->server)(app(AppResolver::class), 'v1');
            $schema = new ($schema)($server);

            $bar->setMessage("Генерация схем $tag");
            $generators->generateSchemas($schema);
            $bar->setMessage("Генерация параметров $tag");
            $generators->generateParameters($schema);
            $bar->setMessage("Генерация ответов $tag");
            $generators->generateResponses($schema);
            $bar->setMessage("Генерация тел запросов $tag");
            $generators->generateRequests($schema);
            $bar->advance();
        }
        $bar->finish();
        $bar = $this->output->createProgressBar(count($tags) + 1);
        $bar->setFormat("%current%/%max% [%bar%] %percent:1s%% %message%\n");
        $bar->start();
        $bar->setMessage("Генерация компонентов путей");
        foreach ($tags as $tag => $schema) {
            if (!class_exists($schema)) {
                continue;
            }
            $server = new ($service->server)(app(AppResolver::class), 'v1');
            $schema = new ($schema)($server);

            $bar->setMessage("Генерация путей $tag");
            $generators->generatePath($schema);
        }

        $bar->setMessage("Генерация OpenApi");
        $generators->generateMainOpenApiFile($tags);
        $bar->setMessage("OpenApi документация успешно сгенерированна");
        $bar->finish();
    }
}
