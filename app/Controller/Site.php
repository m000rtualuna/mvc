<?php

namespace Controller;

use Model\Subdivision;
use Model\User;
use Model\Room;
use Model\Role;
use Model\Subscriber;
use Model\Telephone;
use Src\Request;
use Src\View;
use Src\Auth\Auth;
use Src\Validator\Validator;

class Site
{

    public function subdivision(): string
    {
        $message = '';

        // Обработка добавления подразделения
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subdivision_name'], $_POST['subdivision_type'])) {
            $name = trim($_POST['subdivision_name']);
            $type = trim($_POST['subdivision_type']);

            // Валидация данных
            if (empty($name)) {
                $message = 'Название подразделения не может быть пустым';
            } elseif (empty($type)) {
                $message = 'Тип подразделения не может быть пустым';
            } else {
                // Создаём подразделение
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

        // Обработка добавления помещения
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
        $subscribers = Subscriber::all();

        // Обработка добавления телефона
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset(
                $_POST['telephone_name'],
                $_POST['telephone_phone_number'],
                $_POST['telephone_room_id'],
                $_POST['telephone_subscriber_id']
            )) {
            $name = trim($_POST['telephone_name']);
            $phoneNumber = trim($_POST['telephone_phone_number']);
            $roomId = (int)$_POST['telephone_room_id'];
            $subscriberId = (int)$_POST['telephone_subscriber_id'];

            // Валидация данных
            if (empty($name)) {
                $message = 'Название телефона не может быть пустым';
            } elseif (empty($phoneNumber)) {
                $message = 'Номер телефона не может быть пустым';
            } elseif (!preg_match('/^\d{6,15}$/', $phoneNumber)) {
                $message = 'Номер телефона должен содержать от 6 до 15 цифр';
            } elseif ($roomId <= 0) {
                $message = 'Выберите помещение';
            } elseif ($subscriberId <= 0) {
                $message = 'Выберите абонента';
            } else {
                Telephone::create([
                    'name' => $name,
                    'phone_number' => $phoneNumber,
                    'room_id' => $roomId,
                    'subscriber_id' => $subscriberId
                ]);
            }
        }

        $telephones = Telephone::with(['room', 'subscriber'])->get();
        return (new View())->render('site.telephone', [
            'telephones' => $telephones,
            'rooms' => $rooms,
            'subscribers' => $subscribers,
            'message' => $message
        ]);
    }


    public function subscriber() : string
    {
        $subscribers = Subscriber::all();

        $counts = [];
        $subdivisions = Subdivision::all();
        foreach ($subdivisions as $subdivision) {
            $counts[$subdivision->id] = Subscriber::where('subdivision', $subdivision->id)->count();
        }

        $roomCounts = [];
        $rooms = Room::all();
        foreach ($rooms as $room) {
            $roomCounts[$room->id] = Subscriber::where('room', $room->id)->count();
        }

        return (new View())->render('site.subscriber', [
            'subscribers'  => $subscribers,
            'counts'       => $counts,
            'subdivisions' => $subdivisions,
            'roomCounts'   => $roomCounts,
            'rooms'        => $rooms,
        ]);
    }

    public function user(): string
    {
        // Обрабатываем POST‑запрос на обновление роли
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['role_id'])) {
            $userId = (int)$_POST['user_id'];
            $roleId = (int)$_POST['role_id'];

            // Получаем текущего авторизованного пользователя
            $currentUser = Auth::user();

            // Проверяем: если пользователь пытается изменить свою роль — запрещаем
            if ($currentUser && $userId === $currentUser->id) {
                // Можно добавить сообщение об ошибке в сессию или передать в шаблон
                $_SESSION['error'] = 'Вы не можете изменить свою собственную роль';


                // Перенаправляем обратно на страницу
                header('Location: /users');
                exit;
            }

            // Если это другой пользователь — обновляем роль
            $user = User::find($userId);
            if ($user) {
                $user->updateRole($roleId);
            }

            // После обработки делаем редирект
            header('Location: /users');
            exit;
        }

        // Загружаем актуальные данные для отображения
        $users = User::with(['role'])->get();
        $roles = Role::all();

        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']); // Очищаем сообщение после использования

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
                app()->route->redirect('/signup');
            }
        }
        return new View('site.signup');
    }

    public function login(Request $request): string
    {
        // Если просто обращение к странице, то отобразить форму
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
                // Например, если роль неизвестная, редирект на главную
                app()->route->redirect('/main');
            }
        }

        // Если аутентификация не удалась
        return new View('site.login', ['message' => 'Неправильные логин или пароль']);
    }

    public function logout(): void
    {
        Auth::logout();
        app()->route->redirect('/login');
    }
}