<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('channel', ['boutique_en_ligne', 'vente_manuelle'])
                ->default('boutique_en_ligne')
                ->after('order_number');
        });

        // Schema Builder plutôt qu'un DB::statement() MySQL brut : ce dernier
        // faisait planter toute la suite de tests (sqlite en mémoire) et toute
        // tentative d'installation sur un autre pilote que MySQL.
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_method', ['orange_money', 'wave', 'especes'])->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('channel');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_method', ['orange_money', 'wave'])->nullable()->change();
        });
    }
};
