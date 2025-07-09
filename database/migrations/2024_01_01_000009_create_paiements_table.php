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
        Schema::create('paiements', function (Blueprint $table) {
            $table->id('IdPaiement');
            $table->datetime('DatePaiement')->nullable();
            $table->integer('MontantPaiement');
            $table->string('NumeroFacture', 50)->unique();
            $table->boolean('Statut');
            $table->unsignedBigInteger('IdLocation')->nullable();
            $table->unsignedBigInteger('IdModePaiement')->nullable();
            $table->timestamps();

            $table->foreign('IdLocation')->references('IdLocation')->on('locations')->onDelete('set null');
            $table->foreign('IdModePaiement')->references('IdModePaiement')->on('modepaiements')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
}; 