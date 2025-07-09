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
        Schema::create('appartements', function (Blueprint $table) {
            $table->id('IdAppartement');
            $table->string('AdresseAppartement', 255);
            $table->float('Surface')->nullable();
            $table->integer('NombrePiece')->nullable();
            $table->integer('Capacite');
            $table->boolean('Disponiblite')->default(true);
            $table->integer('nbrLocataire')->default(0);
            $table->unsignedBigInteger('IdProprietaire')->nullable();
            $table->timestamps();

            $table->foreign('IdProprietaire')->references('IdPersonne')->on('proprietaires')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appartements');
    }
}; 