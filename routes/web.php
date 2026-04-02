<?php

use Src\Route;

Route::add('GET', '/hello', [Controller\Site::class, 'hello'])->middleware('auth');
Route::add(['GET', 'POST'], '/signup', [Controller\Site::class, 'signup']);
Route::add(['GET', 'POST'], '/login', [Controller\Site::class, 'login']);
Route::add('GET', '/logout', [Controller\Site::class, 'logout']);
Route::add('GET', '/go', [Controller\Site::class, 'index']);
// Route::add('GET', '/subscribers', [Controller\Site::class, 'subscribers']);
// Route::add('GET', '/telephones', [Controller\Site::class, 'telephones']);
// Route::add('GET', '/rooms', [Controller\Site::class, 'rooms']);
// Route::add('GET', '/users', [Controller\Site::class, 'users']);