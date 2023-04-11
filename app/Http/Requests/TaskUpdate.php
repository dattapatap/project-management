<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskUpdate extends FormRequest
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
            'task_id'                       => 'required|numeric',
            'txt_task_title'                => 'required|string',
            'txt_task_priority'             => 'required|string',
            'txt_task_est_start_date'       => 'required|date|date_format:d/m/Y h:i A',
            'txt_task_est_end_date'         => 'required|date|date_format:d/m/Y h:i A|after:task_est_start_date',
            'txt_task_description'          => 'required|between:10,600000',
            'txt_task_user'                 => 'required'
        ];
    }

}
