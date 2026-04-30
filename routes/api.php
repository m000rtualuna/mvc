<?php

use Src\Route;

Route::add('GET', '/', [Controller\Api::class, 'index']);
Route::add('POST', '/echo', [Controller\Api::class, 'echo']);
Route::add(['POST', 'GET'], '/getSubscribers', [Controller\Api::class, 'getSubscribers']);
Route::add(['POST', 'GET'], '/authorization', [Controller\Api::class, 'authorization']);
Route::add(['POST', 'GET'], '/registration', [Controller\Api::class, 'registration']);
Route::add(['POST', 'GET'], '/logout', [Controller\Api::class, 'logout']);
Route::add(['POST', 'GET', 'DELETE'], '/files/delete/{id}', [Controller\Api::class, 'deleting_file']);
Route::add(['POST', 'GET'], '/files/download/{id}', [Controller\Api::class, 'downloading_file']);
Route::add(['POST', 'GET'], '/files/upload', [Controller\Api::class, 'upload_file']);
Route::add('PATCH', '/files/rename/{id}', [Controller\Api::class, 'rename_file']);
Route::add('GET', '/files/disk', [Controller\Api::class, 'disk']);
Route::add('POST', '/files/{id}/accesses', [Controller\Api::class, 'addAccess']);