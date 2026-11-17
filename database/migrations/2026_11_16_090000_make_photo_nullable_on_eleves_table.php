<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La photo d'un élève est facultative (un import Excel peut ne pas en contenir).
     *
     * L'ancienne migration "2025_05_23_fix_eleves_nullable_columns" est datée AVANT la création
     * de la table "eleves" : sur une base créée de zéro, elle ne fait rien et "photo" reste obligatoire.
     * Cette migration corrige ce cas. Si la colonne est déjà facultative (c'est le cas en
     * production), elle ne fait rien.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('eleves', 'photo')) {
            return;
        }

        $photo = collect(Schema::getColumns('eleves'))->firstWhere('name', 'photo');

        if ($photo && ($photo['nullable'] ?? false)) {
            return;
        }

        Schema::table('eleves', function (Blueprint $table) {
            $table->string('photo')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Volontairement vide : ne jamais re-bloquer les imports sans photo.
    }
};
