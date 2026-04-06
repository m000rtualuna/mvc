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

    public function subdivision(): string
    {
        $message = '';

        $request = new Request($_POST);

        $middleware = new TrimMiddleware();
        $request = $middleware->handle($request);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $request->all();

            $rules = [
                'subdivision_name' => ['required', 'lang', 'unique:subdivisions,name'],
                'subdivision_type' => ['required', 'lang', 'unique:subdivisions,type'],
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['room_name'], $_POST['room_number_room'], $_POST['room_type'], $_POST['room_subdivision_id'])) {
            Room::create([
                'name' => $_POST['room_name'],
                'number_room' => $_POST['room_number_room'],
                'type' => $_POST['room_type'],
                'subdivision_id' => (int)$_POST['room_subdivision_id']
            ]);
            $message = 'Помещение добавлено';
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
                'num' => 'Поле :field не заполнено',
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
        $subdivisions = Subdivision::all();
        $subdivisions = Subdivision::withCount('subscribers')->get();
        $telephones = Telephone::whereNull('subscriber_id')->get();
        $rooms = Room::withCount('subscribers')->get();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                die('Ошибка безопасности: неверный CSRF‑токен');
            }

            $requiredFields = [
                'subscriber_name',
                'subscriber_surname',
                'subscriber_patronymic',
                'subscriber_date_of_birth',
                'subscriber_subdivision_id'
            ];

            foreach ($requiredFields as $field) {
                if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                    die("Не заполнено обязательное поле: $field");
                }
            }

            try {
                $name = trim($_POST['subscriber_name']);
                $surname = trim($_POST['subscriber_surname']);
                $patronymic = trim($_POST['subscriber_patronymic']);
                $date_of_birth = trim($_POST['subscriber_date_of_birth']);
                $subdivision_id = (int)$_POST['subscriber_subdivision_id'];

                if (!Subdivision::find($subdivision_id)) {
                    die('Указанное подразделение не существует');
                }

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

                    // Обновляем только свободные номера
                    Telephone::whereIn('id', $availablePhones)
                        ->update(['subscriber_id' => $subscriber->id]);
                }

                header('Location: ' . $_SERVER['REQUEST_URI']);
                exit;

            } catch (\Exception $e) {
                error_log('Ошибка создания абонента: ' . $e->getMessage());
                error_log('Трассировка: ' . $e->getTraceAsString());
                die('Произошла ошибка при создании абонента. Код ошибки: ' . $e->getCode() . '. Сообщение: ' . $e->getMessage());
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
        return new View('site.main', ['message' => 'У вас нет прав :(']);
    }


    public function signup(Request $request): string
    {
        if ($request->method === 'POST') {
            $data = $request->all();

            $validator = new Validator($data, [
                'login' => [
                    'required',
                    'unique:users,login',
                    'length:5'
                ],
                'password' => [
                    'required',
                    'length:5'
                ]
            ], [
                'required' => 'Поле :field пусто',
                'unique' => 'Поле :field должно быть уникально',
                'length' => 'Поле :field должно быть от :min символов',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorMessage = '';
                foreach ($errors as $field => $messages) {
                    foreach ($messages as $message) {
                        $errorMessage .= $message . '<br>';
                    }
                }
                return new View('site.signup', [
                    'message' => rtrim($errorMessage, '<br>'),
                    'data' => $data
                ]);
            }

            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['avatar'];
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $avatarName = time() . '_' . uniqid() . '.' . $extension;

                $uploadPath = __DIR__ . '/../../public/uploads/avatars/' . $avatarName;

                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    $data['avatar'] = $avatarName;
                }
            }

            try {
                if (User::create($data)) {
                    app()->route->redirect('/login');
                    return '';
                } else {
                    return new View('site.signup', ['message' => 'Ошибка при создании пользователя']);
                }
            } catch (\Exception $e) {
                return new View('site.signup', ['message' => 'Произошла ошибка: ' . $e->getMessage()]);
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