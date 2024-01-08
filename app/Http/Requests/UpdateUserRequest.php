<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use BenSampo\Enum\Rules\Enum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Gate;

class UpdateUserRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        Gate::authorize('update', $this->route('user'));

        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'role' => UserRole::coerce($this->role) ?? $this->route('user')->role,
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
            'name' => 'string',
            'email' => 'email|unique:users,email,'.$this->route('user')->id,
            'password' => 'string|confirmed',
            'birthdate' => 'date',
            'role' => ['required', new Enum(UserRole::class)],
        ];
    }
}
