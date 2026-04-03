<?php

use Src\Route;

Route::add(['GET', 'POST'], '/signup', [Controller\Site::class, 'signup']);
Route::add(['GET', 'POST'], '/login', [Controller\Site::class, 'login']);
Route::add('GET', '/logout', [Controller\Site::class, 'logout']);
Route::add('GET', '/main', [Controller\Site::class, 'main']);

Route::add(['GET', 'POST'], '/subscribers', [Controller\Site::class, 'subscriber'])->middleware('auth:2');
Route::add(['GET', 'POST'], '/rooms', [Controller\Site::class, 'room'])->middleware('auth:2');
Route::add(['GET', 'POST'], '/telephones', [Controller\Site::class, 'telephone'])->middleware('auth:2');
Route::add(['GET', 'POST'], '/subdivisions', [Controller\Site::class, 'subdivision'])->middleware('auth:2');

Route::add(['GET', 'POST'], '/users', [Controller\Site::class, 'user'])->middleware('auth:3');