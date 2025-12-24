<?php

namespace App\Http\Controllers\Api\V1\Attachment;

use App\Actions\Attachment\AddAttachmentAction;
use App\Actions\Attachment\ListAttachmentsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attachment\StoreAttachmentRequest;
use App\Http\Resources\AttachmentResource;
use App\Models\Task;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TaskAttachmentController extends Controller
{
    public function index(Task $task, ListAttachmentsAction $action)
    {
        $this->authorize('viewTask', [Media::class, $task]);

        $media = $action->execute($task);

        return AttachmentResource::collection($media);
    }

    public function store(StoreAttachmentRequest $request, Task $task, AddAttachmentAction $action)
    {
        $this->authorize('uploadToTask', [Media::class, $task]);

        $media = $action->execute(
            model: $task,
            file: $request->file('file'),
            uploadedByUserId: $request->user()->id
        );

        return (new AttachmentResource($media))
            ->response()
            ->setStatusCode(201);
    }
}
