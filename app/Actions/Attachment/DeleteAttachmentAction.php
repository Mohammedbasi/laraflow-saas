<?php

namespace App\Actions\Attachment;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DeleteAttachmentAction
{
    public function execute(Media $media): void
    {
        $media->delete();
    }
}
