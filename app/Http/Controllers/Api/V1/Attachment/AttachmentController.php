<?php

namespace App\Http\Controllers\Api\V1\Attachment;

use App\Actions\Attachment\DeleteAttachmentAction;
use App\Http\Controllers\Controller;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AttachmentController extends Controller
{
    public function destroy(Media $media, DeleteAttachmentAction $action)
    {
        $this->authorize('delete', $media);

        $action->execute($media);

        return response()->noContent();
    }
}
