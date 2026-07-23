<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Arr;

class UpdateProfileAction
{
    public function execute(User $user, array $data): User
    {
        $updates = Arr::only($data, ['name']);

        if (! empty($data['password'])) {
            $updates['password'] = $data['password'];
        }

        if ($updates !== []) {
            $user->update($updates);
        }

        return $user->refresh();
    }
}
