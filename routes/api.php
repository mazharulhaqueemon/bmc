<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\ForgotPasswordController;




Route::post('signup',[AuthController::class,'signup']);
Route::post('login',[AuthController::class,'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout',[AuthController::class,'logout']);

});



