<?php

namespace App\Http\Requests\Attachment;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttachmentRequest extends FormRequest
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
        $maxKb = (int) config('attachments.max_kb', 10240);
        $mimes = (array) config('attachments.allowed_mimes', []);

        return [
            'file' => [
                'required',
                'file',
                "max:{$maxKb}",
                'mimetypes:'.implode(',', $mimes),
            ],
        ];
    }
}
