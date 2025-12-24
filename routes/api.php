<?php

use App\Http\Controllers\Api\V1\ActivityController;
use App\Http\Controllers\Api\V1\Admin\ActivityAdminController;
use App\Http\Controllers\Api\V1\Admin\AdminBillingController;
use App\Http\Controllers\Api\V1\Admin\ImpersonationController;
use App\Http\Controllers\Api\V1\Admin\TenantAdminController;
use App\Http\Controllers\Api\V1\Admin\UserAdminController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\Billing\BillingController;
use App\Http\Controllers\Api\V1\Billing\StripeWebhookController;
use App\Http\Controllers\Api\V1\Comment\CommentController;
use App\Http\Controllers\Api\V1\Comment\ProjectCommentController;
use App\Http\Controllers\Api\V1\Comment\TaskCommentController;
use App\Http\Controllers\Api\V1\InvitationController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\Task\TaskController;
use App\Http\Controllers\Api\V1\Task\TaskReorderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Cashier\Http\Middleware\VerifyWebhookSignature;

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

        Route::post('/impersonations/start', [ImpersonationController::class, 'start']);

        Route::get('/activities', [ActivityAdminController::class, 'index']);

        Route::get('tenants/{tenant}/billing/status', [AdminBillingController::class, 'status']);
        Route::post('tenants/{tenant}/billing/portal', [AdminBillingController::class, 'portal']);
    });

Route::post('/v1/impersonations/stop', [ImpersonationController::class, 'stop'])
    ->middleware(['auth:sanctum']);

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {

        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:auth-login');

        Route::post('/register', [AuthController::class, 'register'])
            ->middleware('throttle:auth-register');

        Route::middleware(['auth:sanctum', 'tenant.context'])->group(function () {

            Route::get('/me', [AuthController::class, 'me']);

            Route::post('/logout', [AuthController::class, 'logout']);

        });
    });

    Route::middleware(['auth:sanctum', 'tenant.context', 'tenant.not_suspended', 'user.active'])->group(function () {

        Route::get('billing/status', [BillingController::class, 'status']);

        Route::post('billing/checkout', [BillingController::class, 'checkout']);

        Route::post('billing/portal', [BillingController::class, 'portal']);

        Route::post('/invitations', [InvitationController::class, 'store'])
            ->middleware(['subscription.limit', 'throttle:invitations']);

        Route::apiResource('projects', ProjectController::class);
        Route::apiResource('projects.tasks', TaskController::class)
            ->shallow();

        // Drag & drop reorder endpoint
        Route::post('projects/{project}/tasks/reorder', TaskReorderController::class);

        // comments
        Route::get('projects/{project}/comments', [ProjectCommentController::class, 'index']);
        Route::post('projects/{project}/comments', [ProjectCommentController::class, 'store']);

        Route::get('tasks/{task}/comments', [TaskCommentController::class, 'index']);
        Route::post('tasks/{task}/comments', [TaskCommentController::class, 'store']);

        Route::put('comments/{comment}', [CommentController::class, 'update']);
        Route::delete('comments/{comment}', [CommentController::class, 'destroy']);

        Route::get('/activities', [ActivityController::class, 'index']);

    });

    Route::post('stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
        ->middleware([VerifyWebhookSignature::class]);

    // Public: signed accept link
    Route::get('/invitations/{invitation}/accept', [InvitationController::class, 'accept'])
        ->name('invitations.accept')
        ->middleware('signed');

    // Public: complete invitation (creates user)
    Route::post('/invitations/complete', [InvitationController::class, 'complete']);

});
