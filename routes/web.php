<?php

use Src\Route;

Route::add('GET', '/hello', [Controller\Site::class, 'hello'])->middleware('auth');
Route::add(['GET', 'POST'], '/signup', [Controller\Site::class, 'signup']);
Route::add(['GET', 'POST'], '/login', [Controller\Site::class, 'login']);
Route::add('GET', '/logout', [Controller\Site::class, 'logout']);
Route::add('GET', '/subdivision', [Controller\Site::class, 'subdivision']);
Route::add('GET', '/telephones', [Controller\Site::class, 'telephone']);
Route::add('GET', '/rooms', [Controller\Site::class, 'room']);
// Route::add('GET', '/users', [Controller\Site::class, 'users']);