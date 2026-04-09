<?php

namespace App\Policies;

use App\Models\Movie;
use App\Models\User;

class MoviePolicy
{   
    // rotas publicas
    public function viewAny(?User $user): bool 
    {
        return true;
    }

    public function view(?User $user, Movie $movie): bool
    {
        return true;
    }

    // rotas autenticadas geridas pelo mid
    public function create(User $user): bool 
    {
        return true;
    }

    public function update(User $user, Movie $movie): bool // comparacao para evitar coercao
    {
        return $user->id === $movie->created_by;
    }

    public function delete(User $user, Movie $movie): bool // comparacao para evitar coercao
    {
        return $user->id === $movie->created_by;
    }
}