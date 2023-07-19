<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/server/pub-key', [\App\Http\Controllers\ServerController::class, 'getPubKey']);
Route::middleware(['client.auth'])->group(function () {
    Route::prefix('auth')->controller(\App\Http\Controllers\AuthController::class)->group(function () {
        Route::post('register', 'register');
        Route::get('info', 'info');
        Route::delete('destroy', 'destroy');
    });
    Route::prefix('messages')->controller(\App\Http\Controllers\MessageController::class)->group(function () {
        Route::post('clear-all', 'clearAll');
        Route::post('send', 'send');
        Route::get('get-list', 'getList');
    });
    Route::prefix('members')->controller(\App\Http\Controllers\MemberController::class)->group(function () {
        Route::get('search', 'search');
    });
    Route::prefix('contacts')->controller(\App\Http\Controllers\ContactController::class)->group(function () {
        Route::get('list', 'getList');
        Route::post('add-friend', 'addFriend');
        Route::post('add-group', 'addGroup');
        Route::get('get-info', 'getInfo');

    });
    Route::prefix('groups')->controller(\App\Http\Controllers\GroupController::class)->group(function () {
        Route::post('create', 'create');
        Route::post('quit', 'quit');
    });
    Route::prefix('invitation-notices')->controller(\App\Http\Controllers\InvitationNoticeController::class)->group(function () {
        Route::get('list', 'getList');
        Route::post('accept', 'accept');
        Route::post('reject', 'reject');
    });
    Route::prefix('file')->controller(\App\Http\Controllers\FileController::class)->group(function () {
        Route::post('upload', 'upload');
    });
});
Route::get('file/download/{id}', [\App\Http\Controllers\FileController::class, 'download']);
