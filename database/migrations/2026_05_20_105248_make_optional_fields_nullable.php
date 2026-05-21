<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table eleves — matricule_edumaster nullable
        Schema::table('eleves', function (Blueprint $table) {
            $table->string('matricule_edumaster')->nullable()->change();
        });

        // Table directeurs — signature et cachet nullable
        Schema::table('directeurs', function (Blueprint $table) {
            $table->string('signature')->nullable()->change();
            $table->string('cachet')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('eleves', function (Blueprint $table) {
            $table->string('matricule_edumaster')->nullable(false)->change();
        });

        Schema::table('directeurs', function (Blueprint $table) {
            $table->string('signature')->nullable(false)->change();
            $table->string('cachet')->nullable(false)->change();
        });
    }
};