<?php
return [
    //Класс аутентификации
    'auth' => Src\Auth\Auth::class,

    //Клас пользователя
    'identity'=>Model\User::class,

    //Классы для middleware
    'routeMiddleware' => [
        'auth' => Middlewares\AuthMiddleware::class,
    ],
    'validators' => [
        'required' => Validators\RequireValidator::class,
        'unique' => Validators\UniqueValidator::class,
        'length' => Validators\LengthValidator::class,
        'lang' => Validators\LangValidator::class,
        'num' => Validators\NumValidator::class,
        'data' => Validators\DateValidator::class,
    ],
    'routeAppMiddleware' => [
        'csrf' => Middlewares\CSRFMiddleware::class,
        'specialChars' => Middlewares\SpecialCharsMiddleware::class,
        'trim' => Middlewares\TrimMiddleware::class,
        'json' => Middlewares\JSONMiddleware::class,
    ],
    'providers' => [
        'kernel' => Providers\KernelProvider::class,
        'route' => Providers\RouteProvider::class,
        'db' => Providers\DBProvider::class,
        'auth' => Providers\AuthProvider::class,
    ],
];