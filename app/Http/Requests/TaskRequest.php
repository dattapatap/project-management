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
            'task_projectid'            => 'required|numeric',
            'task_title'                => 'required|string',
            'task_priority'             => 'required|string',
            'task_est_start_date'       => 'required|date',
            'task_est_end_date'         => 'required|date',
            'task_description'          => 'required|between:10,600000',
            'task_user'                 => 'required'
        ];
    }


}
