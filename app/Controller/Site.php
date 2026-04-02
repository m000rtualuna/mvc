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
        return (new View())->render('site.subscriber', ['subscribers' => $subscribers]);
    }

    public function hello(): string
    {
        return new View('site.hello', ['message' => 'hello working']);
    }

    public function signup(Request $request): string
    {
        if ($request->method === 'POST' && User::create($request->all()))
        {
            app()->route->redirect('/subscribers');
        }
        return new View('site.signup');
    }

    public function login(Request $request): string
    {
        //Если просто обращение к странице, то отобразить форму
        if ($request->method === 'GET') {
            return new View('site.login');
        }
        //Если удалось аутентифицировать пользователя, то редирект
        if (Auth::attempt($request->all())) {
            app()->route->redirect('/subscribers');
        }
        //Если аутентификация не удалась, то сообщение об ошибке
        return new View('site.login', ['message' => 'Неправильные логин или пароль']);
    }

    public function logout(): void
    {
        Auth::logout();
        app()->route->redirect('/hello');
    }
}