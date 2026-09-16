<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->string('youtube_url')->nullable()->after('snapchat_url');
            $table->string('linkedin_url')->nullable()->after('youtube_url');
            $table->string('google_maps_url')->nullable()->after('linkedin_url');
            $table->string('address')->nullable()->after('google_maps_url');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn(['youtube_url', 'linkedin_url', 'google_maps_url', 'address']);
        });
    }
};
