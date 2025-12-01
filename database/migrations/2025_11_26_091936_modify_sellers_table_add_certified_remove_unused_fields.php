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
        Schema::table('sellers', function (Blueprint $table) {
            // Add is_certified field
            $table->boolean('is_certified')->default(false)->after('name');

            // Drop unused fields (keep affiliated_with_seller_id)
            $table->dropColumn([
                'email',
                'phone_number',
                'identification_number',
                'address',
                'notes',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            // Remove is_certified field
            $table->dropColumn('is_certified');

            // Restore dropped fields
            $table->string('email', 255)->nullable()->after('name');
            $table->string('phone_number', 30)->nullable()->after('email');
            $table->string('identification_number', 50)->nullable()->after('phone_number');
            $table->string('address', 255)->nullable()->after('identification_number');
            $table->string('notes', 255)->nullable()->after('address');
        });
    }
};