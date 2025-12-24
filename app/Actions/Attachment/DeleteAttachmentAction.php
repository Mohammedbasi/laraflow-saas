<?php

namespace App\Actions\Attachment;

use App\Support\Audit\AuditLogger;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DeleteAttachmentAction
{
    public function execute(Media $media): void
    {
        $model = $media->model;

        if ($model && class_exists(AuditLogger::class)) {
            app(AuditLogger::class)->log(
                tenantId: $model->tenant_id,
                causerId: auth()->id(),
                subject: $model,
                description: 'attachment_deleted',
                meta: [
                    'media_id' => $media->id,
                    'file_name' => $media->file_name,
                ],
                impersonatedByUserId: optional(auth()->user()?->currentAccessToken())->impersonated_by_user_id
            );
        }

        $media->delete();
    }
}
