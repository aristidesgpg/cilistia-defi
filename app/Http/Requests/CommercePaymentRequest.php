<?php

namespace App\Http\Requests;

use App\Rules\Decimal;
use Illuminate\Foundation\Http\FormRequest;

class CommercePaymentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $basicRules = [
            'title' => 'required|string|max:100',
            'description' => 'required|string|max:500',
            'redirect' => 'nullable|url|max:150',
            'expires_at' => 'nullable|date|after:now',
            'message' => 'nullable|string|max:200',
        ];

        if ($this->isMethod('PATCH')) {
            return $basicRules;
        }

        return array_merge($basicRules, [
            'type' => 'required|in:single,multiple',
            'amount' => ['required', 'numeric', 'gt:0', new Decimal],
            'currency' => 'required|exists:supported_currencies,code',
            'coins' => 'required|array|min:1|max:10',
            'coins.*' => 'required|exists:coins,identifier',
        ]);
    }
}
