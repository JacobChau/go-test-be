<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAssessmentRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'subjectId' => 'required|exists:subjects,id',
            'description' => 'nullable|string',
            'duration' => 'required|integer',
            'totalMarks' => 'required|numeric',
            'passMarks' => 'nullable|numeric',
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
        ];
    }
}
