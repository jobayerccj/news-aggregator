<?php

namespace App\Http\Requests;

use App\Traits\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class RegisterUserRequest extends FormRequest
{
    use ApiResponse;

    /**
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ];
    }

    public function messages()
    {
        return [

        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator);
        //throw new HttpResponseException($this->errorResponse($validator->errors()->first(), Response::HTTP_UNPROCESSABLE_ENTITY, $validator->errors()));
    }
}
