<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDonationItemRequest extends FormRequest
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
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string', 'max:2000'],
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'condition' => ['sometimes', 'required', 'string', 'in:Novo,Usado - Excelente estado,Usado - Bom estado,Usado - Estado regular'],
            'location' => ['sometimes', 'required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['url'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'title.max' => 'O título não pode ter mais de 255 caracteres.',
            'description.required' => 'A descrição é obrigatória.',
            'description.max' => 'A descrição não pode ter mais de 2000 caracteres.',
            'category_id.required' => 'A categoria é obrigatória.',
            'category_id.exists' => 'A categoria selecionada não existe.',
            'condition.required' => 'O estado do item é obrigatório.',
            'condition.in' => 'O estado do item deve ser: Novo, Usado - Excelente estado, Usado - Bom estado ou Usado - Estado regular.',
            'location.required' => 'A localização é obrigatória.',
            'location.max' => 'A localização não pode ter mais de 255 caracteres.',
            'latitude.numeric' => 'A latitude deve ser um número.',
            'latitude.between' => 'A latitude deve estar entre -90 e 90.',
            'longitude.numeric' => 'A longitude deve ser um número.',
            'longitude.between' => 'A longitude deve estar entre -180 e 180.',
            'images.array' => 'As imagens devem ser uma lista.',
            'images.max' => 'Você pode enviar no máximo 5 imagens.',
            'images.*.url' => 'Cada imagem deve ser uma URL válida.',
        ];
    }
}

