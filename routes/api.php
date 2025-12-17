<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\InvitationController;
use App\Http\Controllers\Api\V1\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {

        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);

        Route::middleware(['auth:sanctum', 'tenant.context'])->group(function () {

            Route::get('/me', [AuthController::class, 'me']);

            Route::post('/logout', [AuthController::class, 'logout']);

        });
    });

    Route::middleware(['auth:sanctum', 'tenant.context'])->group(function () {

        Route::post('/invitations', [InvitationController::class, 'store']);

        Route::apiResource('projects', ProjectController::class);
        // tasks later
    });

    // Public: signed accept link
    Route::get('/invitations/{invitation}/accept', [InvitationController::class, 'accept'])
        ->name('invitations.accept')
        ->middleware('signed');

    // Public: complete invitation (creates user)
    Route::post('/invitations/complete', [InvitationController::class, 'complete']);
});
