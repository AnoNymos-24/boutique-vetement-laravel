<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->string('mail_username')->nullable()->after('snapchat_url');
            $table->text('mail_password')->nullable()->after('mail_username');
            $table->string('mail_from_name')->nullable()->after('mail_password');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn(['mail_username', 'mail_password', 'mail_from_name']);
        });
    }
};
