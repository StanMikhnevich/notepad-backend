<?php

namespace App\Http\Requests\Api\Notes;

use App\Http\Requests\BaseFormRequest;

class IndexNotesRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
//        return (bool) $this->isAuthenticated();
        return (bool) true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'show' => 'required|in:all,public,my,shared',
            'per_page' => 'nullable|numeric|min:1|max:100'
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [];
    }
}
