<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('complaint_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            // Motifs alignés sur l'article 9.1 des Conditions générales — un remboursement
            // ne se crée que pour l'un de ces cas, jamais "sans raison".
            $table->enum('reason', [
                'annulation_avant_expedition',
                'produit_defectueux',
                'non_conforme',
                'erreur_commande',
                'indisponibilite',
                'retour_reclamation',
            ]);
            $table->unsignedInteger('amount');
            $table->string('method')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('processed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
