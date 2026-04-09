<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // expoe APENAS o necessario nunca password, remember_token, etc.
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }
}