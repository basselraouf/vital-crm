<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class CreateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'Role name must be at most 30 characters long.',
        ];
    }
}
