<?php

namespace Controller;

use Model\Subdivision;
use Model\User;
use Model\Room;
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
        $room = Room::all();
        return (new View())->render('site.room', ['room' => $room]);
    }

    public function telephone() : string
    {
        $telephone = Telephone::all();
        return (new View())->render('site.telephone', ['telephone' => $telephone]);
    }

    public function hello(): string
    {
        return new View('site.hello', ['message' => 'hello working']);
    }

    public function signup(Request $request): string
    {
        if ($request->method === 'POST' && User::create($request->all()))
        {
            app()->route->redirect('/go');
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
            app()->route->redirect('/hello');
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