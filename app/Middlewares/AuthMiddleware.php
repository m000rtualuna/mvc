<?php

namespace Middlewares;

use Src\Auth\Auth;
use Src\Request;

class AuthMiddleware
{
    public function handle(Request $request, string $requiredRole = null)
    {
        // Если пользователь не авторизован — редирект на страницу входа
        if (!Auth::check()) {
            app()->route->redirect('/login');
        }

        // Если роль не указана — пропускаем дальше
        if (is_null($requiredRole)) {
            return;
        }

        // Получаем роль текущего пользователя
        $userRole = Auth::user()->role_id;
        $requiredRoleId = (int)$requiredRole;

        if ($userRole !== $requiredRoleId) {
            app()->route->redirect('/access-denied');
        }
    }
}