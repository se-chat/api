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
            'type'=>'required|string|in:before,after'
        ];
    }

    public function messages(): array
    {
        return [
            'contact_id.required' => '联系人ID不能为空',
            'contact_id.string' => '联系人ID格式错误',
            'last_id.string' => '最后一条消息ID格式错误',
            'type.required'=>'类型不能为空',
            'type.string'=>'类型格式错误',
            'type.in'=>'类型错误'
        ];
    }
}
