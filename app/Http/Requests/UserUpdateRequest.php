<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
        $id = $this->request->get('user_id');
        return [
            'name' => 'required|string|max:50|unique:users,name,'.$id.',id,deleted_at,NULL',
            'email' => 'required|email|unique:users,email,'.$id.',id,deleted_at,NULL',
            'mobile' => 'required|digits:10|regex:/^[6-9][0-9]{9}/',
            'dob' => 'required|date',
            'role' => 'required',
            'department' => 'required|numeric',
            'designation' => 'required|string',
            'code' => 'required|unique:employees,mem_code,'.$id.',user,deleted_at,NULL',
            'joining_date' => 'required|date',
        ];
    }
}
