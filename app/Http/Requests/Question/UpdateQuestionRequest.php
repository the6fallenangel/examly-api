<?php

namespace App\Http\Requests\Question;

use App\Enums\QuestionType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuestionRequest extends FormRequest
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
        $type = $this->input('type', $this->route('question')?->type?->value);

        $needsOptions = in_array($type, [
            QuestionType::MultipleChoice->value,
            QuestionType::Checkbox->value,
        ]);

        return [
            'type' => ['sometimes', Rule::enum(QuestionType::class)],
            'prompt' => ['sometimes', 'string', 'max:2000'],
            'options' => [Rule::requiredIf($needsOptions), 'array', 'min:2'],
            'options.*' => ['string', 'max:255'],
            'config' => ['nullable', 'array'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_required' => ['sometimes', 'boolean'],
        ];
    }
}
