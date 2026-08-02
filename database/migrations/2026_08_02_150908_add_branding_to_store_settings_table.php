<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('description');
            $table->string('facebook_url')->nullable()->after('logo_path');
            $table->string('instagram_url')->nullable()->after('facebook_url');
            $table->string('tiktok_url')->nullable()->after('instagram_url');
            $table->string('snapchat_url')->nullable()->after('tiktok_url');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'facebook_url', 'instagram_url', 'tiktok_url', 'snapchat_url']);
        });
    }
};
