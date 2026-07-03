<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrivacySettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'show_email' => ['boolean'],
            'show_phone' => ['boolean'],
            'show_location' => ['boolean'],
            'allow_messages' => ['boolean'],
            'online_status' => ['boolean'],
        ];
    }
}
