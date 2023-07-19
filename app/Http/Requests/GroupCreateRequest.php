<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GroupCreateRequest extends FormRequest
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
            'name' => 'required|string|max:20',
            'message_expired_time' => 'required|integer|min:3',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => '群组名称不能为空',
            'name.string' => '群组名称必须是字符串',
            'name.max' => '群组名称最大长度为20',
            'message_expired_time.required' => '消息过期时间不能为空',
            'message_expired_time.integer' => '消息过期时间必须是整数',
            'message_expired_time.min' => '消息过期时间最小值为3',
        ];
    }
}
