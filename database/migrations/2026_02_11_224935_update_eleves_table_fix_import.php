<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eleves', function (Blueprint $table) {

            // ✅ Supprimer numero_table
            if (Schema::hasColumn('eleves', 'numero_table')) {
                $table->dropColumn('numero_table');
            }

            // ✅ Ajouter nationalite
            if (!Schema::hasColumn('eleves', 'nationalite')) {
                $table->string('nationalite')->nullable()->after('sexe');
            }

        });
    }

    public function down(): void
    {
        Schema::table('eleves', function (Blueprint $table) {

            // 🔄 Recréer numero_table si rollback
            if (!Schema::hasColumn('eleves', 'numero_table')) {
                $table->string('numero_table')->unique()->after('matricule_edumaster');
            }

            // 🔄 Supprimer nationalite
            if (Schema::hasColumn('eleves', 'nationalite')) {
                $table->dropColumn('nationalite');
            }

        });
    }
};
