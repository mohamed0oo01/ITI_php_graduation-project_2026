<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'required_skills' => ['required', 'string'],
            'category' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'work_type' => ['required', 'string', 'in:Remote,On-site,Hybrid'],
            'salary' => ['required', 'numeric', 'min:0'],
            'application_deadline' => ['required', 'date', 'after_or_equal:today'],
        ];
    }
}
