<?php

use App\Http\Controllers\Api\V1\Admin\TenantAdminController;
use App\Http\Controllers\Api\V1\Admin\UserAdminController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\InvitationController;
use App\Http\Controllers\Api\V1\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1/admin')
    ->middleware(['auth:sanctum', 'tenant.context', 'role:super_admin'])
    ->group(function () {
        Route::get('/tenants', [TenantAdminController::class, 'index']);
        Route::get('/tenants/{tenant}', [TenantAdminController::class, 'show']);
        Route::patch('/tenants/{tenant}', [TenantAdminController::class, 'update']);
        Route::post('/tenants/{tenant}/suspend', [TenantAdminController::class, 'suspend']);
        Route::post('/tenants/{tenant}/activate', [TenantAdminController::class, 'activate']);

        Route::get('/users', [UserAdminController::class, 'index']);
        Route::get('/users/{user}', [UserAdminController::class, 'show']);
        Route::patch('/users/{user}', [UserAdminController::class, 'update']);

        Route::put('/users/{user}/roles', [UserAdminController::class, 'setRoles']);

        Route::post('/users/{user}/deactivate', [UserAdminController::class, 'deactivate']);
        Route::post('/users/{user}/activate', [UserAdminController::class, 'activate']);

    });

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {

        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);

        Route::middleware(['auth:sanctum', 'tenant.context'])->group(function () {

            Route::get('/me', [AuthController::class, 'me']);

            Route::post('/logout', [AuthController::class, 'logout']);

        });
    });

    Route::middleware(['auth:sanctum', 'tenant.context', 'tenant.not_suspended', 'user.active'])->group(function () {

        Route::post('/invitations', [InvitationController::class, 'store'])
            ->middleware('role:tenant_admin');

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
