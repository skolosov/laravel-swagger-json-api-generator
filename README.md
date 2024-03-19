# laravel-swagger-json-api-generator

## Установка

```php
composer require syn/laravel-swagger-json-api-generator 
```
опубликовать файлы:

```php
php artisan vendor:publish --provider=Syn\LaravelSwaggerJsonApiGenerator\Providers\ServiceProvider
```
### Команда опубликует
* файл кофигурации ***swagger-jsonapi-generator.php***
* директорию ***docs*** для хранения собственных путей и компонентов OpenApi

### важно!!!
Добавить в **config/filesystems.php** новый драйвер хранилища документации

```php
    'docs' => [
        'driver' => 'local',
        'root' => docs_path(),
        'throw' => false,
    ],
```

## Базовое использование

для того что бы сгенерировать документацию по спецификации JsonApi
для маршрутов пакета [laravel-json-api/laravel](https://laraveljsonapi.io/)
необходимо настроить файл конфигурации
### config/swagger-jsonapi-generator.php
```php
<?php


return [

    'title' => 'Backend',
    'version' => '1.0.0',
    'description' => 'Бэкенд тестового проекта',

    /**
     * Серверы спецификации
     *
     * - { url: 'http://localhost:8000/', description: local }
     */
    'servers' => [
        ['url' => 'http://localhost:8000/', 'description' => 'local'],
    ],

    /**
     * Здась описываются ресурсы которые
     * необходимо отрисовать в документации
     * {тип ресурса} => {схема ресурса / тэг который будет отображатся для кастомных методов}
     * 'auth' => 'auth' // регистрация тэга ресурса если у него нет схемы
     * 'users' => UserSchema::class // регистрация тэга ресурса через схему LaravelJsonApi пакета
     */
    'resources' => [
        'auth' => 'auth',
        'users' => App\JsonApi\V1\Users\UserSchema::class
    ],

    /**
     * Ссылка на сервер LaravelJsonApi
     *
     * Пример:
     * 'server' => App\JsonApi\V1\Server::class
     */
    'serverJsonApi' => App\JsonApi\V1\Server::class,

    /**
     * Названия ресурсов которые будут отображатся в группе
     *
     * Пример:
     * 'auth' => 'Аутентификация',
     * 'users' => 'Пользователи',
     */
    'resourceNames' => [
        'auth' => 'Аутентификация',
        'users' => 'Пользователи',
    ]
];
```

и наконец заменить все классы полей в схемах на аналогичные из этого пакета
```php
<?php

namespace App\JsonApi\V1\Users;

use App\Models\User;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;
 - use LaravelJsonApi\Eloquent\Fields\Boolean;
 - use LaravelJsonApi\Eloquent\Fields\DateTime;
 - use LaravelJsonApi\Eloquent\Fields\ID;
 - use LaravelJsonApi\Eloquent\Fields\Relation\BelongsTo;
 - use LaravelJsonApi\Eloquent\Fields\Str;
 - use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
 + use Syn\LaravelSwaggerJsonApiGenerator\Fields\Boolean;
 + use Syn\LaravelSwaggerJsonApiGenerator\Fields\DateTime;
 + use Syn\LaravelSwaggerJsonApiGenerator\Fields\ID;
 + use Syn\LaravelSwaggerJsonApiGenerator\Fields\Relation\BelongsTo;
 + use Syn\LaravelSwaggerJsonApiGenerator\Fields\Str;
 + use Syn\LaravelSwaggerJsonApiGenerator\Filters\WhereIdIn;

class UserSchema extends Schema
{

    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = User::class;

    /**
     * Get the resource fields.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            ID::make(),
            Str::make('name'),
            Str::make('email')
                ->typeUsing('string')
                ->example('test@test.ru')
                ->description('Email пользователя'),

            Boolean::make('is_active')
                ->typeUsing('boolean')
                ->example(false)
                ->description('Активный пользователь'),

            BelongsTo::make('profiles')
                ->relationshipModel(Profile::class),

            DateTime::make('createdAt')->sortable()->readOnly(),
            DateTime::make('updatedAt')->sortable()->readOnly(),
        ];
    }

    /**
     * Get the resource filters.
     *
     * @return array
     */
    public function filters(): array
    {
        return [
            WhereIdIn::make($this)
                ->typeUsing('string')
                ->example('1,2,3,4')
                ->description('фильтр по ID'),
        ];
    }

    /**
     * Get the resource paginator.
     *
     * @return Paginator|null
     */
    public function pagination(): ?Paginator
    {
        return PagePagination::make();
    }

}
```

## Команада для генерации документации
```php
php artisan docs:gen
```
