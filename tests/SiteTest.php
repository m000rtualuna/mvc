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


    #[DataProvider('signupProvider')]
    #[RunInSeparateProcess]
    public function testSignup(string $httpMethod, array $userData, string $message): void
    {
        if ($userData['login'] === 'login is busy') {
            $userData['login'] = User::get()->first()->login;
        }

        $request = $this->createMock(\Src\Request::class);
        $request->expects($this->any())
            ->method('all')
            ->willReturn($userData);
        $request->method = $httpMethod;

        $result = (new \Controller\Site())->signup($request);

        if (!empty($result)) {
            $message = '/' . preg_quote($message, '/') . '/';
            $this->expectOutputRegex($message);
            return;
        }

        $this->assertTrue((bool)User::where('login', $userData['login'])->count());
        User::where('login', $userData['login'])->delete();

    }

    public static function signupProvider(): array
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
        $request = $this->createMock(\Src\Request::class);
        $request->expects($this->any())
            ->method('all')
            ->willReturn($userData);
        $request->method = $httpMethod;

        $result = (new \Controller\Site())->login($request);

        if (!empty($result)) {
            $message = '/' . preg_quote($message, '/') . '/';
            $this->expectOutputRegex($message);
            return;
        }

        $this->assertTrue((bool)User::where('login', $userData['login'])->count());
        User::where('login', $userData['login'])->delete();
    }

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


    #[DataProvider('subdivisionsProvider')]
    #[RunInSeparateProcess]
    public function testSubdivisions(string $httpMethod, array $subdivisionData, string $message): void
    {
        $csrfToken = app()->auth::generateCSRF();
        $subdivisionData['csrf_token'] = $csrfToken;

        if ($subdivisionData['subdivision_name'] === 'subdivision_name is busy') {
            $subdivisionData['subdivision_name'] = Subdivision::get()->first()->name;
        }

        $request = $this->createMock(\Src\Request::class);
        $request->expects($this->any())
            ->method('all')
            ->willReturn($subdivisionData);
        $request->method = $httpMethod;

        $_SERVER['REQUEST_METHOD'] = $httpMethod;
        $_SESSION['csrf_token'] = $csrfToken;

        $result = (new \Controller\Site())->subdivision($request);

        if (!empty($message)) {
            $this->assertStringContainsString($message, $result);
            return;
        }

        $this->assertFalse((bool)Subdivision::where('name', $subdivisionData['subdivision_name'])->count());
    }


    public static function subdivisionsProvider(): array
    {
        return [
            ['GET', ['subdivision_name' => '', 'subdivision_type' => ''], ''],
            ['POST', ['subdivision_name' => '', 'subdivision_type' => 'ещкере'], '<h3>Поле subdivision_name не заполнено</h3>'],
            ['POST', ['subdivision_name' => 'name is busy', 'subdivision_type' => 'не ещкере'], '<h3>Поле subdivision_name должно быть уникальным</h3>'],
        ];
    }

}