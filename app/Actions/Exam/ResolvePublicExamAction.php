<?php

namespace App\Actions\Exam;

use App\Models\Exam;

class ResolvePublicExamAction
{
    public function execute(string $slug): Exam
    {
        return Exam::published()->where('slug', $slug)->firstOrFail();
    }
}
