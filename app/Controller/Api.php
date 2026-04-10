<?php
namespace Controller;
use Model\Subdivision;
use Model\Subscriber;
use Model\Telephone;
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



    public function api_token(Request $request): void
    {
        $token = \Src\Session::get('api_token');

        if ($token) {
            (new View())->toJSON(['api_token' => $token]);
        } else {
            http_response_code(401);
            (new View())->toJSON(['error' => 'not authorized']);
        }
    }


    public function login()
    {
        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Missing Authorization header']);
            exit;
        }

        $authHeader = $headers['Authorization'];

        if (!preg_match('/^Bearer\s+(.*)$/i', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid authorization header format']);
            exit;
        }

        $token = $matches[1];
        $validToken = 'tokkk';

        if ($token !== $validToken) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }

        $sessionToken = bin2hex(random_bytes(16));
        \Src\Session::set('api_token', $sessionToken);

        echo json_encode([
            'session_token' => $sessionToken
        ]);
    }


    public function getSubscribers()
    {

        $headers = getallheaders();
        if (!isset($headers['Authorization']) || $headers['Authorization'] !== 'Bearer tokkk') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $subscribers = Subscriber::with('telephone')->get();
        $result = [];

        foreach ($subscribers as $subscriber) {
            $phoneNumbers = [];

            if ($subscriber->telephone) {
                $phoneNumbers = array_map(function($tel) {
                    return $tel['phone_number'];
                }, $subscriber->telephone->toArray());
            }

            $result[] = [
                'id' => $subscriber->id,
                'name' => $subscriber->name,
                'surname' => $subscriber->surname,
                'patronymic' => $subscriber->patronymic,
                'date_of_birth' => $subscriber->date_of_birth,
                'subdivision_id' => $subscriber->subdivision_id,
                'telephones' => $phoneNumbers
            ];
        }
        (new View())->toJSON($result);
    }
}