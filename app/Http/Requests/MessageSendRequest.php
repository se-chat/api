<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MessageSendRequest extends FormRequest
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
            'type' => 'required|string|in:text,image,file',
            'content' => 'required|string',
            'contact_id' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => '消息类型不能为空',
            'type.string' => '消息类型必须是字符串',
            'type.in' => '消息类型不在指定范围内',
            'content.required' => '消息内容不能为空',
            'content.string' => '消息内容必须是字符串',
            'contact_id.required' => '联系人ID不能为空',
            'contact_id.string' => '联系人ID必须是字符串',
        ];
    }
}
