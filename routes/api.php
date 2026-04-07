<?php

use Src\Route;

Route::add('GET', '/', [Controller\Api::class, 'index']);
Route::add('POST', '/echo', [Controller\Api::class, 'echo']);
//apiii
Route::add('POST', '/login', [Controller\Api::class, 'login']);