<?php
namespace Controller;
use Model\Subdivision;
use Model\User;
use Src\Request;
use Src\View;

class Api
{

    public function index(): void
    {
        $subdivisions = Subdivision::all()->toArray();
        (new View())->toJSON($subdivisions);
    }

    public function echo(Request $request): void
    {
        (new View())->toJSON($request->all());
    }

    // apiii
    public function login(Request $request): void
    {
        $data = $request->all();
        $login = $data['login'] ?? '';
        $password = $data['password'] ?? '';

        $user = User::where('login', $login)->first();

        if ($user && password_verify($password, $user->password)) {
            // Генерируем JWT (без правок БД)
            $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
            $payload = base64_encode(json_encode([
                'user_id' => $user->id,
                'iat' => time() // Делает токен уникальным при каждом входе
            ]));
            $secret = 'any_secret_key_here'; // Просто строка
            $signature = base64_encode(hash_hmac('sha256', "$header.$payload", $secret, true));

            (new View())->toJSON(['token' => "$header.$payload.$signature"]);
            return;
        }

        (new View())->toJSON(['error' => 'Unauthorized'], 401);
    }
}