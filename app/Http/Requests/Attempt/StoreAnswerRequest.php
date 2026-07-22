<?php

namespace App\Http\Requests\Attempt;

use App\Enums\QuestionType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnswerRequest extends FormRequest
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
        $question = $this->route('question');

        return match ($question?->type) {
            QuestionType::MultipleChoice => [
                'response' => ['required', 'string', Rule::in($question->options ?? [])],
            ],
            QuestionType::Checkbox => [
                'response' => ['required', 'array', 'min:1'],
                'response.*' => ['string', Rule::in($question->options ?? [])],
            ],
            QuestionType::Text => [
                'response' => ['required', 'string', 'max:5000'],
            ],
            QuestionType::FileUpload => [
                'response' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,docx', 'max:10240'],
            ],
            default => [
                'response' => ['required'],
            ],
        };
    }
}
