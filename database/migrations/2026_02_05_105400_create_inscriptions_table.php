<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('eleve_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('school_year_id')
                ->constrained('school_years')
                ->cascadeOnDelete();

            $table->foreignId('ecole_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('partition_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // 🔒 Un élève ne peut être inscrit qu'une seule fois par année
            $table->unique(['eleve_id', 'school_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscriptions');
    }
};
