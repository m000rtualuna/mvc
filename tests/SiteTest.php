<?php

use Model\User;
use Model\Subdivision;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;


class SiteTest extends TestCase
{
    protected function setUp(): void
    {
        // установка переменной среды
        $_SERVER['DOCUMENT_ROOT'] = 'C:/OSPanel/home/mvc';

        $config = [
            'app'   => include $_SERVER['DOCUMENT_ROOT'] . '/config/app.php',
            'db'    => include $_SERVER['DOCUMENT_ROOT'] . '/config/db.php',
            'path'  => include $_SERVER['DOCUMENT_ROOT'] . '/config/path.php',
        ];

        if (isset($config['app']['providers'])) {
            $config['providers'] = $config['app']['providers'];
        }

        $GLOBALS['app'] = new Src\Application($config);

        $app = $GLOBALS['app'];

        $settings = new Src\Settings($config);
        $app->bind('settings', $settings);


//Глобальная функция для доступа к объекту приложения
        if (!function_exists('app')) {
            function app()
            {
                return $GLOBALS['app'];
            }
        }
    }


    #[DataProvider('additionProvider')]
    #[RunInSeparateProcess]
    public function testSignup(string $httpMethod, array $userData, string $message): void
    {
//Выбираем занятый логин из базы данных
        if ($userData['login'] === 'login is busy') {
            $userData['login'] = User::get()->first()->login;
        }

// Создаем заглушку для класса Request.
        $request = $this->createMock(\Src\Request::class);
// Переопределяем метод all() и свойство method
        $request->expects($this->any())
            ->method('all')
            ->willReturn($userData);
        $request->method = $httpMethod;

//Сохраняем результат работы метода в переменную
        $result = (new \Controller\Site())->signup($request);

        if (!empty($result)) {
//Проверяем варианты с ошибками валидации
            $message = '/' . preg_quote($message, '/') . '/';
            $this->expectOutputRegex($message);
            return;
        }

//Проверяем добавился ли пользователь в базу данных
        $this->assertTrue((bool)User::where('login', $userData['login'])->count());
//Удаляем созданного пользователя из базы данных
User::where('login', $userData['login'])->delete();

}


//Метод, возвращающий набор тестовых данных
    public static function additionProvider(): array
    {
        return [
            ['GET', ['login' => '', 'password' => ''],
                '<h3></h3>'
            ],
            ['POST', ['login' => '', 'password' => ''],
                '<h3>{"login":["Поле login пусто"],"password":["Поле password пусто"]}</h3>',
            ],
            ['POST', ['login' => 'login is busy',
                'password' => 'eshkere'],
                '<h3>{"login":["Поле login должно быть уникально"]}</h3>',
            ],
        ];
    }





    #[DataProvider('loginProvider')]
    #[RunInSeparateProcess]
    public function testlogin(string $httpMethod, array $userData, string $message): void
    {

// Создаем заглушку для класса Request.
        $request = $this->createMock(\Src\Request::class);
// Переопределяем метод all() и свойство method
        $request->expects($this->any())
            ->method('all')
            ->willReturn($userData);
        $request->method = $httpMethod;

//Сохраняем результат работы метода в переменную
        $result = (new \Controller\Site())->login($request);

        if (!empty($result)) {
//Проверяем варианты с ошибками валидации
            $message = '/' . preg_quote($message, '/') . '/';
            $this->expectOutputRegex($message);
            return;
        }

//Проверяем добавился ли пользователь в базу данных
        $this->assertTrue((bool)User::where('login', $userData['login'])->count());
//Удаляем созданного пользователя из базы данных
        User::where('login', $userData['login'])->delete();

    }


//Метод, возвращающий набор тестовых данных
    public static function loginProvider(): array
    {
        return [
            ['GET', ['login' => '', 'password' => ''],
                '<h3></h3>'
            ],
            ['POST', ['login' => '', 'password' => ''],
                '<h3>{"login":["Поле login пусто"],"password":["Поле password пусто"]}</h3>',
            ]
        ];
    }
}