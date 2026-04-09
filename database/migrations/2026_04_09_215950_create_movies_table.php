<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table)
        {
            $table->id();

            $table->string('title');
            $table->unsignedSmallInteger('release_year');
            $table->string('poster_url')->nullable();
            $table->string('genre')->nullable();
            $table->text('synopsis')->nullable();
            $table->decimal('rating', 3, 1)->nullable();
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete(); // Se o usuario for deletado o filme continua
            $table->timestamps();
            $table->index(['release_year', 'title']); // indice composto para ordens mais comuns
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};