<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'release_year',
        'poster_url',
        'genre',
        'synopsis',
        'rating',
        'created_by',
    ];

    protected $casts = [
        'release_year' => 'integer',
        'rating'       => 'float',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by'); // Informando a FK created_by pq nao vamos usar o padrao
    }


    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when( // when para evitar if/else
            $term,
            fn (Builder $q) => $q->where('title', 'like', "%{$term}%")
        );
    }

    public function scopeSorted(Builder $query, ?string $sort, ?string $dir): Builder
    {
        // Whitelist
        $allowedSorts = ['title', 'release_year', 'created_at', 'rating'];
        $allowedDirs  = ['asc', 'desc'];

        $sort = in_array($sort, $allowedSorts, strict: true) ? $sort : 'release_year';
        $dir  = in_array($dir,  $allowedDirs,  strict: true) ? $dir  : 'desc';

        return $query->orderBy($sort, $dir);
    }
}