<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('orange_money', 'wave', 'especes') NULL");
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('channel');
        });

        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('orange_money', 'wave') NULL");
    }
};
