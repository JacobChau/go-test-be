<?php

namespace App\Http\Requests;

use App\Enums\QuestionType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;

class UpdateQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        Gate::authorize('update', $this->route('question'));

        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'type' => QuestionType::coerce($this->type) ?? null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'content' => 'required|string',
            'explanation.id' => 'exists:question_explanations,id|nullable',
            'explanation.content' => 'string|nullable',
            'categoryId' => 'required|exists:question_categories,id',
            'type' => ['required', new Enum(QuestionType::class)],
        ];

        if ($this->input('type')->key !== QuestionType::fromValue(QuestionType::Text)->key) {
            $rules['options'] = [
                'required',
                'array',
            ];
            $rules['options.*.id'] = 'required|exists:question_options,id';
            $rules['options.*.answer'] = 'required|string';
            $rules['options.*.isCorrect'] = 'required|boolean';
        }

        return $rules;
    }
}