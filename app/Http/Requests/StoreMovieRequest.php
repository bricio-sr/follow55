<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMovieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // autorização feita la no Controller
    }

    public function rules(): array
    {
        return [
            'title'        => ['required', 'string', 'max:255'],
            'release_year' => ['required', 'integer', 'min:1888', 'max:2100'],
            'poster_url'   => ['nullable', 'url', 'max:500'],
            'genre'        => ['nullable', 'string', 'max:100'],
            'synopsis'     => ['nullable', 'string', 'max:5000'],
            'rating'       => ['nullable', 'numeric', 'min:0', 'max:10'],
        ];
    }
}