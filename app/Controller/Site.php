<?php

namespace Controller;

use Model\Subdivision;
use Model\User;
use Model\Room;
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

    public function user() : string
    {
        $users = User::all();
        return (new View())->render('site.user', ['users' => $users]);
    }

    public function hello(): string
    {
        return new View('site.hello', ['message' => 'hello working']);
    }

    public function signup(Request $request): string
    {
        if ($request->method === 'POST' && User::create($request->all())) {
            // Войти под новым пользователем
            Auth::attempt($request->all());

            $user = Auth::user();

            // Редиректы в зависимости от роли
            if ($user->role == 1) {
                app()->route->redirect('/admin-dashboard');
            } elseif ($user->role == 2) {
                app()->route->redirect('/subscribers');
            } else {
                app()->route->redirect('/hello');
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
            if ($user->role == 1) {
                app()->route->redirect('/users');
            } elseif ($user->role == 2) {
                app()->route->redirect('/subscribers');
            } else {
                // Например, если роль неизвестная, редирект на главную
                app()->route->redirect('/hello');
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