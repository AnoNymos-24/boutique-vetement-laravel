<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->string('orange_money_number')->nullable()->after('mail_from_name');
            $table->boolean('orange_money_enabled')->default(true)->after('orange_money_number');
            $table->string('wave_number')->nullable()->after('orange_money_enabled');
            $table->boolean('wave_enabled')->default(true)->after('wave_number');
            $table->boolean('cash_on_delivery_enabled')->default(true)->after('wave_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn([
                'orange_money_number', 'orange_money_enabled',
                'wave_number', 'wave_enabled',
                'cash_on_delivery_enabled',
            ]);
        });
    }
};
