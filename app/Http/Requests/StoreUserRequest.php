<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use BenSampo\Enum\Rules\Enum;
use Illuminate\Contracts\Validation\ValidationRule;

class StoreUserRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'role' => UserRole::coerce($this->role) ?? UserRole::coerce(UserRole::Student),
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
            'name' => 'required|string|max:255',
            'email' => 'email|required|unique:users',
            'password' => 'required|string|min:8',
            'birthdate' => 'date|nullable',
            'role' => ['required', new Enum(UserRole::class)],
        ];
    }
}
