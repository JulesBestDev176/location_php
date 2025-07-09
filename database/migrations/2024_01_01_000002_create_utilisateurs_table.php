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
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->unsignedBigInteger('IdPersonne')->primary();
            $table->string('Identifiant', 50)->unique();
            $table->string('MotDePasse', 255);
            $table->string('profil', 50)->nullable();
            $table->string('Statut', 50)->nullable();
            $table->timestamps();

            $table->foreign('IdPersonne')->references('IdPersonne')->on('personnes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
}; 