<?php

namespace Controller;

use Src\View
class Site
{
    public function index(): void
    {
        $view = new View();
        return $view->render('site.hello', ['message' => 'index working']);
    }
    public function hello(): void
    {
        return new View('site.hello', ['message' => 'hello working']);
    }
}