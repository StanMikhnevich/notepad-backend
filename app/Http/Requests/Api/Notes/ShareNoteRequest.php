<?php

namespace App\Http\Requests\Api\Notes;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;
use App\Models\Note;

class ShareNoteRequest extends BaseFormRequest
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
            'email' => [
                'required',
                Rule::exists('users', 'email')->where(function($query) {
                    $note = Note::where('uid', $this->note_uid)->first();
                    $query->whereNotIn('id', $note->shared()->pluck('user_id')->merge([
                        $this->authUserStrict()->id
                    ]));
                }),
            ],
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'email' => trans('validation.custom.note.share.all'),
        ];
    }
}
