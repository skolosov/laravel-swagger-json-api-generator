<?php


return [

    /** Параметры описания в шапке Swagger'a */
    'title' => 'Backend',
    'version' => '1.0.0',
    'description' => '',

    /**
     * Серверы спецификации
     *
     * - { url: 'http://localhost:8000/', description: local }
     * - { url: 'https://develop-4184.c4.syndev.ru/', description: develop }
     */
    'servers' => [
        ['url' => 'http://localhost:8000/', 'description' => 'local'],
    ],

    /**
     * Здась описываются ресурсы которые
     * необходимо отрисовать в документации
     *
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