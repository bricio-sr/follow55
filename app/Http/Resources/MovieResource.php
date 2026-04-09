<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovieResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'release_year' => $this->release_year,
            'poster_url'   => $this->poster_url,
            'genre'        => $this->genre,
            'synopsis'     => $this->synopsis,
            'rating'       => $this->rating,
            // evita n+1 se nao foi carregado nao aparece no json
            'created_by' => new UserResource($this->whenLoaded('creator')), // só inclui o relacionamento se foi feito
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}