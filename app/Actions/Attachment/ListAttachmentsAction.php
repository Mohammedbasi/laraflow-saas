<?php

namespace App\Actions\Attachment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ListAttachmentsAction
{
    public function execute(Model $model, string $collection = 'attachments'): Collection
    {
        return $model->getMedia($collection);
    }
}
