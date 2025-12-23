<?php

namespace App\Http\Requests\Task;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReorderTasksRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'moves' => ['required', 'array', 'min:1'],
            'moves.*.task_id' => ['required', 'integer', 'exists:tasks,id'],
            'moves.*.status' => ['required', 'string', Rule::in(Task::STATUSES)],
            'moves.*.position' => ['required', 'integer', 'min:1'],
        ];
    }
}
