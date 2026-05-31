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
        Schema::create('colis', function (Blueprint $table) {
            $table->id();
            $table->string('code_suivi')->unique();
            $table->string('nom_destinataire');
            $table->string('telephone_destinataire');
            $table->string('adresse_destinataire');
            $table->string('ville_destinataire');
            $table->decimal('prix_colis', 10, 2)->default(0);
            $table->decimal('frais_livraison', 10, 2)->default(0);
            $table->string('statut')->default('enregistre');
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('livreur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colis');
    }
};
