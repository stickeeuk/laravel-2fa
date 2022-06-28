<?php

namespace Stickee\Laravel2fa\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TwilioSendCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request
     *
     * @return array
     */
    public function rules()
    {
        return [
            'mobile_number' => [
                'required',
                'string',
                // TODO: make sure valid mobile number
                'min:11',
                'max:11',
            ],
        ];
    }
}
