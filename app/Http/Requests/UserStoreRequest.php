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
        $roleId = $this->input('role');
        $role = $roleId ? \Spatie\Permission\Models\Role::find($roleId) : null;
        $isBranchManager = $role && $role->name === 'Branch-Manager';

        return [
            'name' => 'required|string|max:50|unique:users,name,NULL,id,deleted_at,NULL',
            'email' => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'mobile' => 'required|digits:10|regex:/^[6-9][0-9]{9}/|unique:users,mobile,NULL,id,deleted_at,NULL',
            'gender' => 'required',
            'dob' => 'required|date|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
            'role' => 'required',
            'department' => $isBranchManager ? 'nullable' : 'required',
            'designation' => 'required|string',
            'code' => 'required|unique:employees,mem_code,NULL,id,deleted_at,NULL',
            'joining_date' => 'required|date',
            'password' => 'required|confirmed|min:5',
            'password_confirmation' => 'required|min:5',
            'status' => 'nullable|string|in:Active,Probation,Notice Period,Suspended,Resigned,Terminated,Inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'dob.before_or_equal' => 'Date of birth must be at least 18 years ago (minimum 18 years of age required).',
        ];
    }
}
