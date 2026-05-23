<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration corrective — ne supprime aucune donnée.
 *
 * D'après l'historique complet des migrations :
 *  - numero_table     : déjà supprimée (2026_02_11_224935) → on ne touche pas
 *  - nationalite      : déjà ajoutée   (2026_02_11_224935) → on ne touche pas
 *  - matricule_edumaster : NOT NULL UNIQUE → NULLABLE UNIQUE
 *  - photo               : NOT NULL        → NULLABLE
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eleves', function (Blueprint $table) {

            // 1. matricule_edumaster : NOT NULL → NULLABLE
            //    L'index unique existe déjà → on le supprime avant, puis on le recrée.
            if (Schema::hasColumn('eleves', 'matricule_edumaster')) {
                $this->dropIndexIfExists('eleves_matricule_edumaster_unique');
                $table->string('matricule_edumaster')->nullable()->unique()->change();
            }

            // 2. photo : NOT NULL → NULLABLE
            if (Schema::hasColumn('eleves', 'photo')) {
                $table->string('photo')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('eleves', function (Blueprint $table) {

            if (Schema::hasColumn('eleves', 'matricule_edumaster')) {
                $this->dropIndexIfExists('eleves_matricule_edumaster_unique');
                $table->string('matricule_edumaster')->nullable(false)->unique()->change();
            }

            if (Schema::hasColumn('eleves', 'photo')) {
                $table->string('photo')->nullable(false)->change();
            }
        });
    }

    private function dropIndexIfExists(string $indexName): void
    {
        $exists = DB::select("
            SELECT COUNT(*) as cnt
            FROM information_schema.STATISTICS
            WHERE table_schema = DATABASE()
              AND table_name   = 'eleves'
              AND index_name   = ?
        ", [$indexName]);

        if ($exists[0]->cnt > 0) {
            DB::statement("ALTER TABLE `eleves` DROP INDEX `{$indexName}`");
        }
    }
};
