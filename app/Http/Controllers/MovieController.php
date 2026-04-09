<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovieRequest;
use App\Http\Requests\UpdateMovieRequest;
use App\Http\Resources\MovieResource;
use App\Models\Movie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MovieController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Movie::class);

        $perPage = min((int) $request->query('per_page', 15), 100);

        $movies = Movie::query()
            ->with('creator')
            ->search($request->query('search'))
            ->sorted($request->query('sort'), $request->query('dir'))
            ->paginate($perPage);

        return MovieResource::collection($movies);
    }

    public function show(Movie $movie): JsonResponse
    {
        $this->authorize('view', $movie);

        $movie->load('creator');

        return response()->json([
            'data' => new MovieResource($movie),
        ]);
    }

    public function store(StoreMovieRequest $request): JsonResponse
    {
        $this->authorize('create', Movie::class);

        // created_by vem do usuario autenticado e nunca da requisicao
        $movie = Movie::create(
            array_merge(
                $request->validated(),
                ['created_by' => $request->user()->id]
            )
        );

        $movie->load('creator');

        return response()->json([
            'data'    => new MovieResource($movie),
            'message' => 'Movie created successfully.',
        ], 201);
    }

    public function update(UpdateMovieRequest $request, Movie $movie): JsonResponse
    {
        $this->authorize('update', $movie);

        $movie->update($request->validated());

        $movie->load('creator');

        return response()->json([
            'data'    => new MovieResource($movie),
            'message' => 'Movie updated successfully.',
        ]);
    }

    public function destroy(Movie $movie): JsonResponse
    {
        $this->authorize('delete', $movie);

        $movie->delete();

        return response()->json([
            'message' => 'Movie deleted successfully.',
        ]);
    }
}