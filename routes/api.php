<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\Api\CarrierFilterController;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\JsonUnauthorized;
use App\Http\Middleware\CheckRole;

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


Route::middleware([JsonUnauthorized::class])->group(function () {
    Route::controller(CarrierFilterController::class)->group(function () {
        Route::get('/filterzap/{telefone}', 'filterWpp');
        Route::get('/filterport/{telefone}', 'filterPortabilidade');
        Route::get('/files', 'userFiles');
        Route::get('/download/{uuid}', 'downloadForUUID');

        Route::post('/filter', 'filterCarrier');
        Route::post('/filterzap', 'whatsappemLote');
        Route::post('/import-phone-list', 'importPhoneList');

        Route::delete('/files/delete/{uuid}', 'deleteFileForUUID');
    });
});

Route::controller(UserController::class)->group(function () {
    Route::get('/users', 'index')->middleware(CheckRole::class);
    Route::get('/users/{id}', 'show')->middleware(CheckRole::class);

    Route::put('/users/{id}', 'update')->middleware(CheckRole::class);

    Route::post('/logout', 'logout')->middleware(CheckRole::class);
    Route::post('/refresh', 'refresh')->middleware(CheckRole::class);
    Route::post('/users', 'store')->middleware(CheckRole::class);

    Route::delete('/users/delete/{id}', 'destroy')->middleware(CheckRole::class);
    
})->middleware(JsonUnauthorized::class);


Route::controller(PlanController::class)->group(function () {
    Route::post('/usersToPlan', 'purchasePlan')->middleware(CheckRole::class);

    Route::post('/WhatsappQueries', 'addWhatsappQueries')->middleware(CheckRole::class);

    Route::get('/verifybalance/{id}', 'QueryBalanceUser')->middleware(CheckRole::class);
})->middleware(JsonUnauthorized::class);



