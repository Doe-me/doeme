<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email_new_message' => ['boolean'],
            'email_donation_interest' => ['boolean'],
            'email_newsletter' => ['boolean'],
            'push_new_message' => ['boolean'],
            'push_donation_interest' => ['boolean'],
            'push_new_reviews' => ['boolean'],
        ];
    }
}
