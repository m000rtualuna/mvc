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
    ],
    'routeAppMiddleware' => [
        'csrf' => Middlewares\CSRFMiddleware::class,
        'trim' => Middlewares\TrimMiddleware::class,
        'specialChars' => Middlewares\SpecialCharsMiddleware::class,
    ],
];