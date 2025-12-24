<?php

namespace App\Http\Controllers\Api\V1\Attachment;

use App\Actions\Attachment\AddAttachmentAction;
use App\Actions\Attachment\ListAttachmentsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attachment\StoreAttachmentRequest;
use App\Http\Resources\AttachmentResource;
use App\Models\Project;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProjectAttachmentController extends Controller
{
    public function index(Project $project, ListAttachmentsAction $action)
    {
        $this->authorize('viewProject', [Media::class, $project]);

        $media = $action->execute($project);

        return AttachmentResource::collection($media);
    }

    public function store(StoreAttachmentRequest $request, Project $project, AddAttachmentAction $action)
    {
        $this->authorize('uploadToProject', [Media::class, $project]);

        $media = $action->execute(
            model: $project,
            file: $request->file('file'),
            uploadedByUserId: $request->user()->id
        );

        return (new AttachmentResource($media))
            ->response()
            ->setStatusCode(201);
    }
}
