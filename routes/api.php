<?php

use Src\Route;

Route::add('GET', '/', [Controller\Api::class, 'index']);
Route::add('POST', '/echo', [Controller\Api::class, 'echo']);
Route::add(['POST', 'GET'], '/getSubscribers', [Controller\Api::class, 'getSubscribers']);
Route::add(['POST', 'GET'], '/login', [Controller\Api::class, 'login']);