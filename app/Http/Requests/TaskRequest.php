<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'project'        => 'required|numeric',
            'title'          => 'required|string',
            'description'    => 'nullable|between:10,60000',
            'priority'       => 'required|string',
            'startdate'      => 'nullable|date',
            'enddate'        => 'nullable|date',
        ];
    }


}
