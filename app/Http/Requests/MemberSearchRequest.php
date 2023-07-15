<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MemberSearchRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {

        return [
            'q' => 'required|string',
            'group_id' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'q.required' => '请输入搜索关键字',
            'q.string' => '搜索关键字格式错误',
            'group_id.integer' => '群组ID格式错误',
        ];
    }
}
