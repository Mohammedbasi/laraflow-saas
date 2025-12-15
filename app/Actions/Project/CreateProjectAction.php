<?php

namespace App\Actions\Project;

use App\Models\Project;

class CreateProjectAction
{
    public function execute(array $data): Project
    {
        return Project::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'pending',
        ]);
    }
}
