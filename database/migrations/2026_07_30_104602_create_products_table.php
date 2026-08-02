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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->text('description');
            $table->unsignedInteger('price'); // en centimes, partagé par toutes les variantes
            $table->unsignedInteger('compare_at_price')->nullable(); // prix barré (promo)
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->timestamps();
            // SKU et stock vivent sur product_variants : un produit a toujours au moins une variante.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
