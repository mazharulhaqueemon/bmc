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
use App\Http\Controllers\API\ProfilePictureController;
use App\Http\Controllers\API\PaymentController;

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
    Route::post('/admin/plan', [PlanController::class, 'create']); // create plan
    Route::get('/admin/plans', [PlanController::class, 'index']);   // list plans
});



Route::middleware('auth:sanctum')->group(function () {
    Route::post('/phone-requests/send', [PhoneRequestController::class, 'sendRequest']);
    Route::post('/phone-requests/{id}/respond', [PhoneRequestController::class, 'respondRequest']);
    Route::get('/phone-requests', [PhoneRequestController::class, 'listRequests']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('profile-pictures/upload', [ProfilePictureController::class, 'upload']);
    Route::get('profile-pictures', [ProfilePictureController::class, 'list']);
    Route::delete('profile-pictures/{id}', [ProfilePictureController::class, 'delete']);
    Route::post('profile-pictures/{id}/primary', [ProfilePictureController::class, 'setPrimary']);
});


Route::middleware('auth:sanctum')->group(function() {
    // User submits payment request
    Route::post('/payment/submit', [PaymentController::class, 'submit']);
    
    // User lists their own payments
    Route::get('/payment/user', [PaymentController::class, 'userPayments']);
});
