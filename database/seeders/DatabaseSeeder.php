<?php

namespace Database\Seeders;

use App\Models\Movie;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $alice = User::firstOrCreate(
            ['email' => 'alice@example.com'],
            ['name' => 'Alice Silva', 'password' => Hash::make('password')]
        );

        $bob = User::firstOrCreate(
            ['email' => 'bob@example.com'],
            ['name' => 'Bob Costa', 'password' => Hash::make('password')]
        );

        $aliceMovies = [
            ['title' => 'Blade Runner',        'release_year' => 1982, 'genre' => 'Sci-Fi',  'rating' => 8.1],
            ['title' => '2001: A Space Odyssey','release_year' => 1968, 'genre' => 'Sci-Fi',  'rating' => 8.3],
            ['title' => 'The Godfather',        'release_year' => 1972, 'genre' => 'Crime',   'rating' => 9.2],
            ['title' => 'Pulp Fiction',         'release_year' => 1994, 'genre' => 'Crime',   'rating' => 8.9],
        ];

        $bobMovies = [
            ['title' => 'Inception',     'release_year' => 2010, 'genre' => 'Action',  'rating' => 8.8],
            ['title' => 'The Dark Knight','release_year' => 2008, 'genre' => 'Action',  'rating' => 9.0],
            ['title' => 'Interstellar',  'release_year' => 2014, 'genre' => 'Sci-Fi',  'rating' => 8.7],
            ['title' => 'Parasite',      'release_year' => 2019, 'genre' => 'Thriller','rating' => 8.5],
        ];

        foreach ($aliceMovies as $data) {
            Movie::firstOrCreate(
                ['title' => $data['title'], 'created_by' => $alice->id],
                array_merge($data, ['created_by' => $alice->id])
            );
        }

        foreach ($bobMovies as $data) {
            Movie::firstOrCreate(
                ['title' => $data['title'], 'created_by' => $bob->id],
                array_merge($data, ['created_by' => $bob->id])
            );
        }

        $this->command->info('Seed concluído!');
        $this->command->info('alice@example.com / password');
        $this->command->info('bob@example.com   / password');
        $this->command->info(Movie::count() . ' filmes criados.');
    }
}
