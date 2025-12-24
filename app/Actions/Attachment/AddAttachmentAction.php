<?php

namespace App\Actions\Attachment;

use App\Support\Audit\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AddAttachmentAction
{
    public function execute(Model $model, UploadedFile $file, int $uploadedByUserId, string $collection = 'attachments'): Media
    {
        $media = $model
            ->addMedia($file)
            ->withCustomProperties([
                'uploaded_by_user_id' => $uploadedByUserId,
            ])
            ->toMediaCollection($collection);

        if (class_exists(AuditLogger::class)) {
            app(AuditLogger::class)->log(
                tenantId: $model->tenant_id,
                causerId: $uploadedByUserId,
                subject: $model,
                description: 'attachment_uploaded',
                meta: [
                    'media_id' => $media->id,
                    'file_name' => $media->file_name,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                ],
                impersonatedByUserId: optional(auth()->user()?->currentAccessToken())->impersonated_by_user_id
            );
        }

        return $media;
    }
}
