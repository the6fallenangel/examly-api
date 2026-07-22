<?php

namespace App\Actions\Attempt;

use App\Models\Attempt;
use App\Models\Exam;
use Illuminate\Auth\Access\AuthorizationException;

class EnsureAttemptAccessAction
{
    public function execute(Exam $exam, Attempt $attempt, ?string $token): void
    {
        if ($attempt->exam_id !== $exam->id) {
            abort(404);
        }

        if (blank($token) || ! hash_equals((string) $attempt->token, (string) $token)) {
            throw new AuthorizationException('invalid attempt token');
        }

        if ($attempt->verified_at === null) {
            throw new AuthorizationException('attempt is not verified');
        }

        if ($attempt->completed_at !== null) {
            throw new AuthorizationException('this attempt has already been completed');
        }
    }
}
