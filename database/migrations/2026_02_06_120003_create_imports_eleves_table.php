<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imports_eleves', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('ecole_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();

            // Fichier source
            $table->string('original_filename');
            $table->string('file_path');

            // Données extraites (tableau élèves)
            $table->json('payload'); 
            // ex: [{nom, prenom, sexe, ...}]

            // Statut du traitement
            $table->enum('status', [
                'uploaded',   // fichier uploadé
                'previewed',  // données affichées
                'validated',  // prêtes à être enregistrées
                'imported'    // élèves créés
            ])->default('uploaded');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imports_eleves');
    }
};
