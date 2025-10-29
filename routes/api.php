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
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\EducationController;
use App\Http\Controllers\API\CareerController;
use App\Http\Controllers\API\FamilyDetailController;
use App\Http\Controllers\API\LocationController;
use App\Http\Controllers\API\LifestyleController;
use App\Http\Controllers\API\PartnerPreferenceController;


Route::post('signup', [AuthController::class, 'signup']);
Route::post('login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
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

Route::middleware('auth:sanctum')->group(function () {

    Route::get('profiles/search', [ProfileController::class, 'advancedSearch']);

    Route::apiResource('profiles', ProfileController::class);
    Route::apiResource('educations', EducationController::class)->except(['destroy']);
    Route::apiResource('careers', CareerController::class)->except(['destroy']);
    Route::apiResource('family-details', FamilyDetailController::class)->except(['destroy']);
    Route::apiResource('locations', LocationController::class)->except(['destroy']);
    Route::apiResource('lifestyles', LifestyleController::class)->except(['destroy']);
    Route::apiResource('partner-preferences', PartnerPreferenceController::class)->except(['destroy']);
});
