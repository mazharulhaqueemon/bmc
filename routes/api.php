<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\ForgotPasswordController;

use App\Http\Controllers\API\FirebaseController;
use App\Http\Controllers\API\ChatController;
use App\Http\Controllers\API\PlanController;
use App\Http\Controllers\API\PhoneRequestController;


Route::post('signup',[AuthController::class,'signup']);
Route::post('login',[AuthController::class,'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout',[AuthController::class,'logout']);

});

Route::get('/verify-firebase-token', [FirebaseController::class, 'verifyFirebaseToken']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/chat/send', [ChatController::class, 'sendMessage']);
    Route::get('/chat/history/{receiverId}', [ChatController::class, 'getChatHistory']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/phone-requests/send', [PhoneRequestController::class, 'sendRequest']);
    Route::post('/phone-requests/{id}/respond', [PhoneRequestController::class, 'respondRequest']);
    Route::get('/phone-requests', [PhoneRequestController::class, 'listRequests']);
});