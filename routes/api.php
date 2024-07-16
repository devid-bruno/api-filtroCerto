<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CarrierFilterController;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\JsonUnauthorized;

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


Route::post('/auth', [UserController::class, 'authenticate']);


Route::controller(CarrierFilterController::class)->group(function () {
    Route::get('/filterzap/{telefone}', 'filterWpp');
    Route::get('/filterport/{telefone}', 'filterPortabilidade');
    Route::get('/files', 'userFiles');
    Route::get('/download/{uuid}', 'downloadForUUID');

    Route::post('/filter', 'filterCarrier');
    Route::post('/filterzap', 'whatsappemLote');
    Route::post('/import-phone-list', 'importPhoneList');

    Route::delete('/files/delete/{uuid}', 'deleteFileForUUID');
    
    
})->middleware(JsonUnauthorized::class);

Route::controller(UserController::class)->group(function () {
    Route::get('/users', 'index');
    Route::get('/users/{id}', 'show');

    Route::put('/users/{id}', 'update');

    Route::post('/logout', 'logout');
    Route::post('/refresh', 'refresh');
    Route::post('/users', 'store');

    Route::delete('/users/delete/{id}', 'destroy');
    
})->middleware(JsonUnauthorized::class);



