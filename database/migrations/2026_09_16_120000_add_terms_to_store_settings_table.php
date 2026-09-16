<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->longText('terms_content')->nullable()->after('cash_on_delivery_enabled');
            $table->timestamp('terms_updated_at')->nullable()->after('terms_content');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn(['terms_content', 'terms_updated_at']);
        });
    }
};
