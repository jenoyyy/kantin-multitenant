<?php

namespace App\Modules\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageBank', $this->route('tenant')) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'bank_name' => ['required', 'string', 'max:100'],
            'account_holder_name' => ['required', 'string', 'max:120'],
            'account_number' => ['required', 'digits_between:6,20'],
        ];
    }
}