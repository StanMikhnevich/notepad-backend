<?php

namespace App\Http\Requests\Notes;

use App\Http\Requests\BaseFormRequest;

class StoreNoteRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|min:5|max:100',
            'text' => 'required|string|min:5|max:2000',
            'private' => 'boolean',
            'attachment' => 'nullable|array',
            'attachment.*' => 'file|mimetypes:text/*,image/*,audio/*,video/*|max:2048',
        ];
    }
}
