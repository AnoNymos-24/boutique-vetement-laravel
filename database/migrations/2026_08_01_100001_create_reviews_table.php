<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            // Achat vérifié si rattaché à une commande réelle du client.
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();

            // Nom affiché même sans compte client (avis transcrit depuis WhatsApp/réseaux).
            $table->string('author_name');
            $table->unsignedTinyInteger('rating');
            $table->text('comment');
            $table->enum('status', ['en_attente', 'publie', 'rejete'])->default('en_attente');

            $table->text('admin_reply')->nullable();
            $table->timestamp('replied_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
