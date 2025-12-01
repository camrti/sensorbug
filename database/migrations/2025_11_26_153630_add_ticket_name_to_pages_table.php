<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('ticket_name', 255)->nullable()->after('page_url');
        });

        // Migrate data: copy notes to ticket_name
        DB::table('pages')->whereNotNull('notes')->update([
            'ticket_name' => DB::raw('notes'),
            'notes' => null
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore data: copy ticket_name back to notes
        DB::table('pages')->whereNotNull('ticket_name')->update([
            'notes' => DB::raw('ticket_name')
        ]);

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('ticket_name');
        });
    }
};