<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('ecoles', function (Blueprint $table) {
        $table->id();
        $table->string('nom_ecole');
        $table->string('adresse_ecole');
        $table->string('telephone');
        $table->string('numero_autorisation')->unique();
        $table->foreignId('user_id')
        ->unique()
        ->constrained()
        ->cascadeOnDelete();

        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecoles');
    }
};
