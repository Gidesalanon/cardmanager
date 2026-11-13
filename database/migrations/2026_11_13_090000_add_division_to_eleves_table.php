<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Division / groupe facultatif d'un élève au sein de sa classe (M1, M2, A, B...).
     * Colonne nullable : aucune donnée existante n'est modifiée.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('eleves', 'division')) {
            Schema::table('eleves', function (Blueprint $table) {
                $table->string('division', 12)->nullable()->after('classe_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('eleves', 'division')) {
            Schema::table('eleves', function (Blueprint $table) {
                $table->dropColumn('division');
            });
        }
    }
};
