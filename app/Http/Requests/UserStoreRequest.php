<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Validator;

class UserStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:50|unique:users,name,NULL,id,deleted_at,NULL',
            'email' => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'mobile' => 'required|digits:10|regex:/^[6-9][0-9]{9}/|unique:users,mobile,NULL,id,deleted_at,NULL',
            'gender' => 'required',
            'dob' => 'required|date',
            'role' => 'required',
            'department' => 'required',
            'designation' => 'required|string',
            'code' => 'required|unique:employees,mem_code,NULL,id,deleted_at,NULL',
            'joining_date' => 'required|date',
            'password' => 'required|confirmed|min:5',
            'password_confirmation' => 'required|min:5',
        ];
    }
}
