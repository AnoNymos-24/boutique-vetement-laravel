<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();

            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_address')->nullable();

            $table->enum('status', ['en_attente', 'confirmee', 'preparee', 'expediee', 'livree', 'annulee'])
                ->default('en_attente');

            $table->enum('payment_status', ['en_attente_verification', 'verifie', 'rejete'])
                ->default('en_attente_verification');
            $table->enum('payment_method', ['orange_money', 'wave'])->nullable();
            $table->string('payment_reference', 6)->nullable();

            $table->unsignedInteger('total'); // en centimes
            $table->string('note')->nullable();

            // Horodatage de chaque étape — donne une timeline sans table d'historique séparée.
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
