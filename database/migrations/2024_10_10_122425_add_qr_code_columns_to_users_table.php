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
        Schema::table('users', function (Blueprint $table) {
            $table->text('qr_code_data')->nullable()->after('municipality'); // Stores the QR code image data
            $table->string('qr_code_id', 50)->nullable()->after('qr_code_data'); // Stores the unique QR code identifier
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['qr_code_data', 'qr_code_id']);
        });
    }
};
