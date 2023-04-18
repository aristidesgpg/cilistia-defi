<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommerceAccountRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|min:10|max:250',
            'website' => 'nullable|url|max:250',
            'email' => 'required|email|max:250',
            'phone' => ['nullable', Rule::phone()->detect()],
            'about' => 'nullable|string|max:1000',
        ];
    }
}
