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
use App\Http\Controllers\API\ProfilePictureController;
use App\Http\Controllers\API\PaymentController;

use App\Http\Controllers\API\ChatListController;
use App\Http\Controllers\API\UserController;

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


Route::get('/plans/public', [PlanController::class, 'index']); 

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

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/payment/submit', [PaymentController::class, 'submit']);

    Route::get('/payment/user', [PaymentController::class, 'userPayments']);
    Route::get('profiles/search', [ProfileController::class, 'advancedSearch']);

    Route::get('profiles/user/{user_id}', [ProfileController::class, 'showByUser']);
    Route::get('educations/profile/{profile_id}', [EducationController::class, 'showByProfile']);
    Route::get('careers/profile/{profile_id}', [CareerController::class, 'showByProfile']);
    Route::get('family-details/profile/{profile_id}', [FamilyDetailController::class, 'showByProfile']);
    Route::get('locations/profile/{profile_id}', [LocationController::class, 'showByProfile']);
    Route::get('lifestyles/profile/{profileId}', [LifestyleController::class, 'showByProfile']);
    Route::get('partner-preferences/profile/{profileId}', [PartnerPreferenceController::class, 'showByProfile']);

    Route::apiResource('profiles', ProfileController::class);
    Route::apiResource('educations', EducationController::class)->except(['destroy']);
    Route::apiResource('careers', CareerController::class)->except(['destroy']);
    Route::apiResource('family-details', FamilyDetailController::class)->except(['destroy']);
    Route::apiResource('locations', LocationController::class)->except(['destroy']);
    Route::apiResource('lifestyles', LifestyleController::class)->except(['destroy']);
    Route::apiResource('partner-preferences', PartnerPreferenceController::class)->except(['destroy']);
});



Route::middleware('auth:sanctum')->group(function () {
    Route::get('/chat/list', [ChatListController::class, 'index']);
    Route::post('/chat/list', [ChatListController::class, 'store']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/me', [UserController::class, 'me']);
});
