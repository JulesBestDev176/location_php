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
        Schema::create('locations', function (Blueprint $table) {
            $table->id('IdLocation');
            $table->string('NumeroLocation', 50)->unique();
            $table->integer('MontantLocation');
            $table->date('DateDebut');
            $table->date('DateFin')->nullable();
            $table->datetime('DateCreation');
            $table->boolean('Statut')->default(true);
            $table->unsignedBigInteger('IdAppartement')->nullable();
            $table->unsignedBigInteger('IdLocataire')->nullable();
            $table->timestamps();

            $table->foreign('IdAppartement')->references('IdAppartement')->on('appartements')->onDelete('cascade');
            $table->foreign('IdLocataire')->references('IdPersonne')->on('locataires')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
}; 