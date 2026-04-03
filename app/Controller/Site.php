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

class Site
{
    public function subdivision(): string
    {
        $subdivisions = Subdivision::all();
        return (new View())->render('site.subdivision', ['subdivisions' => $subdivisions]);
    }

    public function room() : string
    {
        $rooms = Room::all();
        return (new View())->render('site.room', ['room' => $rooms]);
    }

    public function telephone() : string
    {
        $telephones = Telephone::with(['subscriber', 'room'])->get();
        return (new View())->render('site.telephone', ['telephones' => $telephones]);
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
        $errors = [];

        if ($request->method === 'POST') {
            $login = $request->get('login') ?? '';
            $password = $request->get('password') ?? '';

            if (strlen($login) < 5 || strlen($login) > 15) {
                $errors[] = 'Логин должен быть от 5 до 15 символов';
            }

            if (User::where('login', $login)->first()) {
                $errors[] = 'Пользователь с таким логином уже существует';
            }

            if (strlen($password) < 5) {
                $errors[] = 'Пароль должен быть не менее 5 символов';
            }

            if (empty($errors)) {
                if (User::create($request->all())) {
                    Auth::attempt($request->all());
                    app()->route->redirect('/main');
                }
            }
        }

        // Передаем ошибки в представление
        return new View('site.signup', [
            'errors' => $errors,
            'message' => !empty($errors) ? 'Ошибка регистрации' : ''
        ]);
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