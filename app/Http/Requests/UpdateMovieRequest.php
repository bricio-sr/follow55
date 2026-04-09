<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMovieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'sometimes' é para validar se o campo vier na requisicao
            // permite atualizar apenas os campos enviados
            'title'        => ['sometimes', 'required', 'string', 'max:255'],
            'release_year' => ['sometimes', 'required', 'integer', 'min:1888', 'max:2100'],
            'poster_url'   => ['sometimes', 'nullable', 'url', 'max:500'],
            'genre'        => ['sometimes', 'nullable', 'string', 'max:100'],
            'synopsis'     => ['sometimes', 'nullable', 'string', 'max:5000'],
            'rating'       => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:10'],
        ];
    }
}