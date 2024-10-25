<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramController;

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

Route::post('/webhook', [TelegramController::class, 'handleWebhook'])->name('telegram_webhook');
Route::get('/webhook/set', [TelegramController::class, 'setWebhook']);
Route::get('/webhook/remove', [TelegramController::class, 'removeWebhook']);
