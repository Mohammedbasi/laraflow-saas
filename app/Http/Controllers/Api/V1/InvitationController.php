<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Invitation\CompleteInvitationAction;
use App\Actions\Invitation\CreateInvitationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Invitation\CompleteInvitationRequest;
use App\Http\Requests\Invitation\StoreInvitationRequest;
use App\Http\Resources\UserResource;
use App\Mail\TenantInvitationMail;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class InvitationController extends Controller
{
    public function store(StoreInvitationRequest $request, CreateInvitationAction $action)
    {
        $this->authorize('create', Invitation::class);

        $user = $request->user();

        [$invitation, $plainToken] = $action->execute(
            tenantId: $user->tenant_id,
            invitedByUserId: $user->id,
            email: $request->validated()['email'],
            role: $request->validated()['role'] ?? 'member'
        );

        // Signed URL to verify invitation (GET)
        $inviteUrl = URL::temporarySignedRoute(
            name: 'invitations.accept',
            expiration: $invitation->expires_at,
            parameters: [
                'invitation' => $invitation->id,
                'token' => $plainToken,
            ]
        );

        Mail::to($invitation->email)->queue(
            new TenantInvitationMail($inviteUrl, $user->tenant->name)
        );

        return response()->json([
            'message' => 'Invitation sent.',
            'invite_url' => $inviteUrl, // temporarily include for testing
        ], 201);
    }

    /**
     * Email link hits this (signed GET). Returns invitation details for UI.
     */
    public function accept(Request $request, Invitation $invitation)
    {
        // Route has 'signed' middleware, so signature is validated already.
        if ($invitation->isAccepted()) {
            return response()->json(['message' => 'Invitation already accepted.'], 410);
        }

        if ($invitation->isExpired()) {
            return response()->json(['message' => 'Invitation expired.'], 410);
        }

        return response()->json([
            'invitation_id' => $invitation->id,
            'email' => $invitation->email,
            'tenant_id' => $invitation->tenant_id,
            'role' => $invitation->role,
        ]);
    }

    /**
     * Completes invitation: creates user + assigns role + returns token.
     */
    public function complete(CompleteInvitationRequest $request, CompleteInvitationAction $action)
    {
        $data = $request->validated();

        $invitation = Invitation::query()->findOrFail($data['invitation_id']);

        $result = $action->execute(
            invitation: $invitation,
            plainToken: $data['token'],
            name: $data['name'],
            password: $data['password'],
            deviceName: $data['device_name'] ?? 'api'
        );

        return response()->json([
            'token' => $result['token'],
            'user' => (new UserResource($result['user']))->resolve(),
        ], 201);
    }
}
