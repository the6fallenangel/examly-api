<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginAction
{
    public function execute(
        string $email,
        string $password
    ): User {
        $cred = [
            'email' => $email,
            'password' => $password,
        ];

        if (! Auth::attempt($cred)) {
            throw ValidationException::withMessages([
                'email' => [
                    'The provided credentials are incorrect.',
                ],
            ]);
        }

        return Auth::user();
    }
}
