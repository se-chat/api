<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MessageGetListRequest extends FormRequest
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
            'contact_id' => 'required|string',
            'last_id' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'contact_id.required' => '联系人ID不能为空',
            'contact_id.string' => '联系人ID必须是字符串',
            'last_id.string' => '最后一条消息ID必须是字符串',
        ];
    }
}
