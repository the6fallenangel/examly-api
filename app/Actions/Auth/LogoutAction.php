<?php

namespace App\Actions\Auth;

use Illuminate\Http\Request;

class LogoutAction
{
    public function execute(Request $req): void
    {
        $req->user()->currentAccessToken()->delete();
    }
}
