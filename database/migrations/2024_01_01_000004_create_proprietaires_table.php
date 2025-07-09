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
        Schema::create('proprietaires', function (Blueprint $table) {
            $table->unsignedBigInteger('IdPersonne')->primary();
            $table->string('Ninea', 50);
            $table->string('Rccm', 50);
            $table->timestamps();

            $table->foreign('IdPersonne')->references('IdPersonne')->on('personnes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proprietaires');
    }
}; 