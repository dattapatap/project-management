<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientUpdate extends FormRequest
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
        $id = $this->request->get('id');
        return [
            'name' => 'required|string|max:50|unique:users,name,'.$id.',id,deleted_at,NULL',
            'category'       => 'required|string',

            'contact_person' => 'nullable|string',
            'designation'    => 'nullable|string',
            'email'          => 'nullable|email',
            'alternate_email'=> 'nullable|email',
            'mobile'         => 'required|digits:10|regex:/^[6-9][0-9]{9}/',
            'alternate_mobile' => 'nullable|digits:10|regex:/^[6-9][0-9]{9}/',

            'telephone'           => 'nullable|regex:/^[0-9]\d{2,4}-\d{6,8}$/',
            'alternate_telephone' => 'nullable|regex:/^[0-9]\d{2,4}-\d{6,8}$/',

            'city'           => 'required|string',
            'website_link'   => 'nullable|url',
            'address1'       => 'required|string',
            'address2'       => 'nullable|string',
            'description'    => 'nullable|string',
        ];
    }
}
