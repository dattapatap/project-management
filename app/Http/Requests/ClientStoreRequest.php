<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientStoreRequest extends FormRequest
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
            'name'           => 'required|string|max:50|unique:clients,name,NULL,id,deleted_at,NULL',
            'category'       => 'required|string',

            'contact_person' => 'nullable|string',
            'designation'    => 'nullable|string',
            'email'          => 'nullable|email',
            'mobile'         => 'required|digits:10|regex:/^[6-9][0-9]{9}/',

            'city'           => 'required|string',
            'website_link'   => 'nullable|url',
            'referral'       => 'required|numeric',
            'address'        => 'required|string',
            'remarks'        => 'required|string',
            'type'           => 'required',
            'time'           => 'required|date_format:h:i A',
            'tbro_date'      => 'nullable|date',
            'status'         => 'required',
        ];
    }
}
