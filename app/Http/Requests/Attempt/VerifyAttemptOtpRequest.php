<?php

namespace App\Http\Requests\Attempt;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VerifyAttemptOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:255'],
            'otp' => ['required', 'digits:6'],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
