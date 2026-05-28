<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eleves', function (Blueprint $table) {
            // Nullable, pas unique — plusieurs élèves peuvent avoir le même N° table selon l'école
            $table->string('numero_table', 20)->nullable()->after('matricule_edumaster');
        });
    }

    public function down(): void
    {
        Schema::table('eleves', function (Blueprint $table) {
            $table->dropColumn('numero_table');
        });
    }
};