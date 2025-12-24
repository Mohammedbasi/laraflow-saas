<?php

namespace App\Actions\Attachment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AddAttachmentAction
{
    public function execute(Model $model, UploadedFile $file, int $uploadedByUserId, string $collection = 'attachments'): Media
    {
        return $model
            ->addMedia($file)
            ->withCustomProperties([
                'uploaded_by_user_id' => $uploadedByUserId,
            ])
            ->toMediaCollection($collection);
    }
}
