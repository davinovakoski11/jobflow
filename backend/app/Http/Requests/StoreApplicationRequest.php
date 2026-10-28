<?php

namespace App\Http\Requests;

use App\Models\JobApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'company' => ['required', 'string', 'max:120'],
            'role' => ['required', 'string', 'max:160'],
            'location' => ['nullable', 'string', 'max:160'],
            'url' => ['nullable', 'url:http,https', 'max:2048'],
            'salary_min' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'status' => ['required', Rule::in(JobApplication::STATUSES)],
            'notes' => ['nullable', 'string', 'max:5000'],
            'applied_on' => ['required', 'date_format:Y-m-d'],
            'remote' => ['required', 'boolean'],
        ];
    }
}
