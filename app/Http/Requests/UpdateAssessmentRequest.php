<?php

namespace App\Http\Requests;

use App\Enums\ResultDisplayMode;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;

class UpdateAssessmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        Gate::authorize('update', $this->route('assessment'));

        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'resultDisplayMode' => ResultDisplayMode::coerce($this->resultDisplayMode) ?? null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'subjectId' => 'required|exists:subjects,id',
            'description' => 'nullable|string',
            'duration' => 'nullable|integer',
            'totalMarks' => 'nullable|numeric',
            'passMarks' => 'nullable|numeric|lte:totalMarks',
            'maxAttempts' => 'nullable|integer',
            'validFrom' => 'nullable|date',
            'validTo' => 'nullable|date',
            'isPublished' => 'required|boolean',
            'questions' => 'required|array',
            'questions.*.id' => 'required|exists:questions,id',
            'questions.*.marks' => 'required|numeric',
            'questions.*.order' => 'required|integer',
            'groupIds' => 'nullable|array',
            'groupIds.*' => 'required_with:groupIds|exists:groups,id',
            'requiredMark' => 'nullable|boolean',
            'resultDisplayMode' => ['required_with:requiredMark', new Enum(ResultDisplayMode::class)],
        ];
    }
}
