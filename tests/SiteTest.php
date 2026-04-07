<?php

use Model\User;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;


class SiteTest extends TestCase
{
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

//Проверяем редирект при успешной регистрации
        $this->assertContains($message, xdebug_get_headers());
}

    protected function setUp(): void
    {
//Установка переменной среды
        $_SERVER['DOCUMENT_ROOT'] = 'home/mvc';

//Создаем экземпляр приложения
        $GLOBALS['app'] = new Src\Application(new Src\Settings([
            'app' => include $_SERVER['DOCUMENT_ROOT'] . '/config/app.php',
            'db' => include $_SERVER['DOCUMENT_ROOT'] . '/config/db.php',
            'path' => include $_SERVER['DOCUMENT_ROOT'] . '/config/path.php',
        ]));

//Глобальная функция для доступа к объекту приложения
        if (!function_exists('app')) {
            function app()
            {
                return $GLOBALS['app'];
            }
        }
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
                'password' => 'admin'],
                '<h3>{"login":["Поле login должно быть уникально"]}</h3>',
            ],
            ['POST', ['login' => md5(time()),
                'password' => 'admin'],
                'Location: /mvc/signup',
            ],
        ];
    }


    public function testSubdivisionCreation(string $httpMethod, array $subdivisionData, string $expectedMessage): void
    {
        // Создаем заглушку Request
        $request = $this->createMock(\Src\Request::class);
        $request->expects($this->any())
            ->method('all')
            ->willReturn($subdivisionData);
        $request->method = $httpMethod;

        // Вызываем контроллер
        $response = (new \Controller\Site())->subdivision($request);

        // Если ожидается сообщение об ошибке
        if (!empty($response)) {
            $messageRegex = '/' . preg_quote($expectedMessage, '/') . '/';
            $this->expectOutputRegex($messageRegex);
            return;
        }

        // Проверяем, что подразделение добавилось
        // Считаем, что есть модель Subdivision
        $exists = \Model\Subdivision::where('name', $subdivisionData['name'])->exists();
        $this->assertTrue($exists, "Subdivision with name '{$subdivisionData['name']}' should exist in DB.");

        // Удаляем созданное подразделение для чистоты тестов
        \Model\Subdivision::where('name', $subdivisionData['name'])->delete();

        // Проверка редиректа при успехе
        $headers = xdebug_get_headers();
        $this->assertContains($expectedMessage, $headers);
    }

    public static function subdivisionProvider(): array
    {
        return [
            ['GET', ['name' => '', 'type' => ''],
                '<h3></h3>'
            ],
            ['POST', ['name' => '', 'type' => ''],
                '<h3>{"name":["Поле name пусто"],"type":["Поле type пусто"]}</h3>',
            ],
            ['POST', ['name' => 'name is busy',
                'type' => 'admin'],
                '<h3>{"name":["Поле name должно быть уникально"]}</h3>',
            ],
            ['POST', ['name' => md5(time()),
                'type' => 'type'],
                'Location: /mvc/subdivisions',
            ],
        ];
    }
}