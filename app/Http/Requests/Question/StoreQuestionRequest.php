<?php

namespace App\Http\Requests\Question;

use App\Enums\QuestionType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('exam'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $needsOptions = in_array($this->input('type'), [
            QuestionType::MultipleChoice->value,
            QuestionType::Checkbox->value,
        ]);

        return [
            'type' => ['required', Rule::enum(QuestionType::class)],
            'prompt' => ['required', 'string', 'max:2000'],
            'options' => [Rule::requiredIf($needsOptions), 'array', 'min:2'],
            'options.*' => ['string', 'max:255'],
            'config' => ['nullable', 'array'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_required' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('is_required')) {
            $this->merge([
                'is_required' => true,
            ]);
        }
    }
}
