<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'donation_item_id' => ['required', 'exists:donation_items,id'],
            'reviewed_user_id' => ['required', 'exists:users,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'donation_item_id.required' => 'O item de doação é obrigatório.',
            'donation_item_id.exists' => 'O item de doação selecionado não existe.',
            'reviewed_user_id.required' => 'O usuário a ser avaliado é obrigatório.',
            'reviewed_user_id.exists' => 'O usuário selecionado não existe.',
            'rating.required' => 'A nota é obrigatória.',
            'rating.integer' => 'A nota deve ser um número inteiro.',
            'rating.between' => 'A nota deve estar entre 1 e 5.',
            'comment.max' => 'O comentário não pode ter mais de 500 caracteres.',
        ];
    }
}

