<?php

use App\Http\Controllers\FileController;
use App\Http\Middleware\GuestSession;
use Illuminate\Support\Facades\Route;


Route::get('/files/list', [FileController::class, 'getUserFiles'])->middleware(GuestSession::class);
Route::post('/files/edit/{id}', [FileController::class, 'handleFile'])->middleware(GuestSession::class);
Route::post('/file/remove', [FileController::class, 'removeUserFile'])->middleware(GuestSession::class);
Route::post('/files/upload', [FileController::class, 'handleFile'])->middleware(GuestSession::class);

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
