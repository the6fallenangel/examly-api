<?php

namespace App\Actions\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutAction
{
    public function execute(Request $req): void
    {
        if (auth()->check()) {
            Auth::guard('web')->logout();
            $req->session()->invalidate();
            $req->session()->regenerateToken();
        }
    }
}
