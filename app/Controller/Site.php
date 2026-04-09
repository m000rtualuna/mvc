<?php

namespace Controller;

use Model\Role;
use Model\Room;
use Model\Subdivision;
use Model\Subscriber;
use Model\Telephone;
use Model\User;
use Src\Auth\Auth;
use Src\Request;
use Src\View;
use Validator\Validator;
use Middlewares\TrimMiddleware;

class Site
{

    public function subdivision(Request $request): string
    {
        $message = '';

        $httpMethod = $request->method ?? $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $middleware = new TrimMiddleware();
        $request = $middleware->handle($request);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $request->all();

            $rules = [
                'subdivision_name' => ['required', 'lang', 'unique:subdivisions,name'],
                'subdivision_type' => ['required', 'lang'],
            ];

            $messages = [
                'required' => 'Поле :field не заполнено',
                'lang' => 'Поле :field должно содержать только кириллицу',
                'unique' => 'Поле :field должно быть уникальным'
            ];

            $validator = new Validator($data, $rules, $messages);

            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorMessage = '';

                foreach ($errors as $field => $messages) {
                    foreach ($messages as $msg) {
                        $errorMessage .= $msg . '<br>';
                    }
                }

                $message = rtrim($errorMessage, '<br>');
            } else {
                $name = $data['subdivision_name'];
                $type = $data['subdivision_type'];

                Subdivision::create([
                    'name' => $name,
                    'type' => $type
                ]);
                $message = 'Подразделение добавлено';
            }
        }

        $subdivisions = Subdivision::all();

        return (new View())->render('site.subdivision', [
            'subdivisions' => $subdivisions,
            'message' => $message
        ]);
    }


    public function room(): string
    {
        $message = '';
        $subdivisions = Subdivision::all();

        $request = new Request($_POST);

        $middleware = new TrimMiddleware();
        $request = $middleware->handle($request);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $request->all();

            $rules = [
                'room_name'           => ['required', 'lang'],
                'room_number_room'    => ['required', 'num', 'unique:rooms,number_room'],
                'room_type'           => ['required', 'lang'],
                'room_subdivision_id' => ['required'],
            ];

            $messages = [
                'required' => 'Поле :field не заполнено',
                'lang' => 'Поле :field должно содержать только кириллицу',
                'unique' => 'Поле :field должно быть уникальным',
                'num' => 'Поле :field должно быть числом',
            ];

            $validator = new Validator($data, $rules, $messages);

            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorMessage = '';
                foreach ($errors as $field => $msgs) {
                    foreach ($msgs as $msg) {
                        $errorMessage .= $msg . '<br>';
                    }
                }
                $message = rtrim($errorMessage, '<br>');
            } else {
                Room::create([
                    'name' => $data['room_name'],
                    'number_room' => $data['room_number_room'],
                    'type' => $data['room_type'],
                    'subdivision_id' => (int)$data['room_subdivision_id']
                ]);
                $message = 'Помещение добавлено';
            }
        }

        $rooms = Room::with('subdivision')->get();
        return (new View())->render('site.room', [
            'rooms' => $rooms,
            'subdivisions' => $subdivisions,
            'message' => $message
        ]);
    }


    public function telephone(): string
    {
        $message = '';
        $rooms = Room::all();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $request = new Request($_POST);

            $middleware = new TrimMiddleware();
            $request = $middleware->handle($request);

            $data = $request->all();

            $rules = [
                'telephone_phone_number' => ['required', 'num', 'unique:telephones,phone_number'],
                'telephone_room_id' => ['required']
            ];

            $messages = [
                'required' => 'Поле :field не заполнено',
                'num' => 'Поле :field может содержать только цифры',
                'unique' => 'Поле :field должно быть уникальным'
            ];

            $validator = new Validator($data, $rules, $messages);

            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorMessage = '';

                foreach ($errors as $field => $messages) {
                    foreach ($messages as $msg) {
                        $errorMessage .= str_replace(':field', $this->getFieldLabel($field), $msg) . '<br>';
                    }
                }

                $message = rtrim($errorMessage, '<br>');
            } else {
                $phoneNumber = $data['telephone_phone_number'];
                $roomId = (int)$data['telephone_room_id'];

                Telephone::create([
                    'phone_number' => $phoneNumber,
                    'room_id' => $roomId,
                ]);
                $message = 'Телефон добавлен';
            }
        }

        $telephones = Telephone::with(['room'])->get();
        return (new View())->render('site.telephone', [
            'telephones' => $telephones,
            'rooms' => $rooms,
            'message' => $message
        ]);
    }

    private function getFieldLabel(string $field): string
    {
        $labels = [
            'telephone_phone_number' => 'Номер телефона',
            'telephone_room_id' => 'Наименование помещения'
        ];

        return $labels[$field] ?? $field;
    }


    public function subscriber(): string
    {
        $message = '';
        $subscribersQuery = Subscriber::with(['subdivision', 'telephone']);

        if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
            $searchTerm = trim($_GET['search']);
            $subscribersQuery->where(function($query) use ($searchTerm) {
                $query->where('name', 'like', "%$searchTerm%")
                    ->orWhere('surname', 'like', "%$searchTerm%")
                    ->orWhere('patronymic', 'like', "%$searchTerm%");
            });
        }

        if (isset($_GET['department_id']) && !empty($_GET['department_id'])) {
            $departmentId = (int)$_GET['department_id'];
            $subscribersQuery->where('subdivision_id', $departmentId);
        }

        $subscribers = $subscribersQuery->get();

        $counts = [];
        $subdivisions = Subdivision::withCount('subscribers')->get();
        $telephones = Telephone::whereNull('subscriber_id')->get();
        $rooms = Room::withCount('subscribers')->get();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                die('Ошибка безопасности: неверный CSRF‑токен');
            }

            $request = new Request($_POST);
            $middleware = new TrimMiddleware();
            $request = $middleware->handle($request);
            $data = $request->all();

            $rules = [
                'subscriber_name' => ['required', 'lang'],
                'subscriber_surname' => ['required', 'lang'],
                'subscriber_patronymic' => ['required', 'lang'],
                'subscriber_date_of_birth' => ['required'],
                'subscriber_subdivision_id' => ['required'],
            ];

            $messages = [
                'required' => 'Поле :field не заполнено',
                'lang' => 'Поле :field должно содержать только кириллицу',
            ];

            $validator = new Validator($data, $rules, $messages);

            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorMessage = '';
                foreach ($errors as $field => $msgs) {
                    foreach ($msgs as $msg) {
                        $errorMessage .= $msg . '<br>';
                    }
                }
                $message = rtrim($errorMessage, '<br>');
            } else {
                try {
                    $name = trim($data['subscriber_name']);
                    $surname = trim($data['subscriber_surname']);
                    $patronymic = trim($data['subscriber_patronymic']);
                    $date_of_birth = trim($data['subscriber_date_of_birth']);
                    $subdivision_id = (int)$data['subscriber_subdivision_id'];

                    $subscriber = Subscriber::create([
                        'name' => $name,
                        'surname' => $surname,
                        'patronymic' => $patronymic,
                        'date_of_birth' => $date_of_birth,
                        'subdivision_id' => $subdivision_id,
                    ]);

                    if (isset($_POST['phone_ids']) && is_array($_POST['phone_ids'])) {
                        $selectedPhoneIds = array_map('intval', $_POST['phone_ids']);

                        $availablePhones = Telephone::whereIn('id', $selectedPhoneIds)
                            ->whereNull('subscriber_id')
                            ->pluck('id')
                            ->toArray();

                        if (empty($availablePhones)) {
                            die('Выбранные номера уже заняты');
                        }

                        Telephone::whereIn('id', $availablePhones)
                            ->update(['subscriber_id' => $subscriber->id]);
                    }

                    header('Location: ' . $_SERVER['REQUEST_URI']);
                    exit;

                } catch (\Exception $e) {
                    error_log('Ошибка создания абонента: ' . $e->getMessage());
                    error_log('Трассировка: ' . $e->getTraceAsString());
                    die('Произошла ошибка при создании абонента.');
                }
            }
        }

        $phonesByDepartment = [];
        if (isset($_GET['department_id']) && !empty($_GET['department_id'])) {
            $departmentId = (int)$_GET['department_id'];
            $phonesByDepartment = Subscriber::with(['telephone', 'subdivision'])
                ->where('subdivision_id', $departmentId)
                ->get();
        }

        return (new View())->render('site.subscriber', [
            'subscribers' => $subscribers,
            'counts' => $counts,
            'subdivisions' => $subdivisions,
            'telephones' => $telephones,
            'phonesByDepartment' => $phonesByDepartment,
            'rooms' => $rooms,
            'message' => $message,
        ]);
    }


    public function user(): string
    {
        // Обработка изменения роли
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['role_id'])) {
            $userId = (int)$_POST['user_id'];
            $roleId = (int)$_POST['role_id'];

            $currentUser = Auth::user();

            if ($currentUser && $userId === $currentUser->id) {
                $_SESSION['error'] = 'Вы не можете изменить свою собственную роль';
                header('Location: /users');
                exit;
            }

            $user = User::find($userId);
            if ($user) {
                $user->updateRole($roleId);
            }

            header('Location: /users');
            exit;
        }

        // Загрузка данных для отображения
        $users = User::with(['role'])->get();
        $roles = Role::all();

        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);

        return (new View())->render('site.user', [
            'users' => $users,
            'roles' => $roles,
            'error' => $error
        ]);
    }


    public function main(): string
    {
        return new View('site.main', ['message' => 'Здесь пусто :(']);
    }


    public function signup(Request $request): string
    {
        if ($request->method === 'POST') {
            $validator = new Validator($request->all(), [
                'login' => ['required', 'unique:users,login'],
                'password' => ['required']
            ], [
                'required' => 'Поле :field пусто',
                'unique' => 'Поле :field должно быть уникально'
            ]);
            if($validator->fails()){
                return new View('site.signup',
                    ['message' => json_encode($validator->errors(),
                        JSON_UNESCAPED_UNICODE)]);
            }
            if (User::create($request->all())) {
                header('Location: /login');
                exit; // гарантированно останавливаем выполнение
                // app()->route->redirect('/login'); // можно убрать или оставить как запасной вариант
            }
        }
        return new View('site.signup');
    }

    public function login(Request $request): string
    {
        if ($request->method === 'GET') {
            return new View('site.login');
        }

        // Пытаемся аутентифицировать пользователя
        if (Auth::attempt($request->all())) {
            $user = Auth::user();

            // В зависимости от роли делаем редирект
            if ($user->role_id == 3) {
                app()->route->redirect('/users');
            } elseif ($user->role_id == 2) {
                app()->route->redirect('/subscribers');
            } else {
                app()->route->redirect('/');
            }
        }

        return new View('site.login', ['message' => 'Неправильные логин или пароль']);
    }


    public function logout(): void
    {
        Auth::logout();
        app()->route->redirect('/login');
    }
}