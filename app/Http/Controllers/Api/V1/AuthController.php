<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Auth\LoginAction;
use App\Actions\Auth\LogoutAction;
use App\Actions\Tenant\RegisterTenantAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterTenantRequest;
use App\Http\Resources\TenantResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(LoginRequest $request, LoginAction $action)
    {
        $data = $request->validated();

        $result = $action->execute(
            $data['email'],
            $data['password'],
            $data['device_name'] ?? 'api',
        );

        return response()->json([
            'token' => $result['token'],
            'user' => new UserResource($result['user']),
        ]);
    }

    public function register(RegisterTenantRequest $request, RegisterTenantAction $action)
    {
        $result = $action->execute($request->validated());

        return response()->json([
            'token' => $result['token'],
            'tenant' => (new TenantResource($result['tenant']))->resolve(),
            'user' => (new UserResource($result['user']))->resolve(),
        ], 201);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function logout(Request $request, LogoutAction $action)
    {
        $action->execute($request->user());

        return response()->noContent();
    }
}
