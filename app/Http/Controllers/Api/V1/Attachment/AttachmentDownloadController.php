<?php

namespace App\Http\Controllers\Api\V1\Attachment;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AttachmentDownloadController extends Controller
{
    public function __invoke(Media $media)
    {
        $this->authorize('view', $media);

        $disk = $media->disk;
        $relativePath = $media->getPathRelativeToRoot();

        
        $storage = Storage::disk($disk);

        // Try to return a temporary URL if supported (private S3/MinIO)
        try {
            // Some disks may not support this; will throw
            $temporaryUrl = $storage->temporaryUrl($relativePath, now()->addMinutes(10));

            return response()->json([
                'data' => [
                    'mode' => 'temporary_url',
                    'temporary_url' => $temporaryUrl,
                    'expires_in_minutes' => 10,
                ],
            ]);
        } catch (\Throwable $e) {
            // fall back to streaming
        }

        abort_unless($storage->exists($relativePath), 404);

        // Stream download via Laravel (works on local/public disks)
        return $storage->download($relativePath, $media->file_name);
    }
}
