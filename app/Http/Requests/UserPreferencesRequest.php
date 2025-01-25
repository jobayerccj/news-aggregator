<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserPreferencesRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'authors' => 'array|exists:authors,id',
            'sources' => 'array|exists:sources,id',
            'categories' => 'array|exists:categories,id',
        ];
    }

    /**
     * Customize error messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'authors.array' => 'Authors must be an array.',
            'authors.exists' => 'One or more authors are invalid.',
            'sources.array' => 'Sources must be an array.',
            'sources.exists' => 'One or more sources are invalid.',
            'categories.array' => 'Categories must be an array.',
            'categories.exists' => 'One or more categories are invalid.',
        ];
    }
}
