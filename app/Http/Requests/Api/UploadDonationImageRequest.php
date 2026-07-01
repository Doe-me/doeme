<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UploadDonationImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'max:5'],
            'images.*' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'images.required' => 'Pelo menos uma imagem é obrigatória.',
            'images.max' => 'Você pode enviar no máximo 5 imagens por vez.',
            'images.*.mimes' => 'Apenas imagens JPG, PNG, WebP ou GIF são permitidas.',
            'images.*.max' => 'Cada imagem deve ter no máximo 5MB.',
        ];
    }
}
