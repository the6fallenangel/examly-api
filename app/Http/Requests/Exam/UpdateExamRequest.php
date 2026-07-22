<?php

namespace App\Http\Requests\Exam;

use App\Enums\ExamStatus;
use App\Models\Exam;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateExamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $examId = $this->route('exam')?->id;

        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['sometimes', Rule::enum(ExamStatus::class)],
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique(Exam::class)->ignore($examId),
            ],
            'published_at' => ['nullable', 'date', 'after_or_equal:now'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('title') && blank($this->slug)) {
            $this->merge([
                'slug' => Str::slug($this->title),
            ]);
        }
    }
}
