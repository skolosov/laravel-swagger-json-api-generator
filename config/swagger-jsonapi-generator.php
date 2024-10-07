<?php


return [

    /** Параметры описания в шапке Swagger'a */
    'page_title' => 'Swagger',
    'title' => 'Backend',
    'version' => '1.0.0',
    'description' => '',

    /**
     * Формат выходного файла спецификации поддерживает формат yaml и json
     *
     */
    'output_format' => 'yaml',
    'output_path' => docs_path('v1'),

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
     *
     * {тип ресурса} => {схема ресурса / тэг который будет отображатся для кастомных методов}
     * 'auth' => 'auth' // регистрация тэга ресурса если у него нет схемы
     * 'users' => UserSchema::class // регистрация тэга ресурса через схему LaravelJsonApi пакета
     */
    'resources' => [
        //
    ],

    /**
     * Ссылка на сервер LaravelJsonApi
     *
     * Пример:
     * 'server' => App\JsonApi\V1\Server::class
     */
    'serverJsonApi' => null,

    /**
     * Названия ресурсов которые будут отображатся в группе
     *
     * Пример:
     * 'auth' => 'Аутентификация',
     * 'users' => 'Пользователи',
     */
    'resourceNames' => [
        //
    ]
];