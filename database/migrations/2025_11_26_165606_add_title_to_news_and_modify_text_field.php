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
        // Step 1: Add title column as nullable
        Schema::table('news', function (Blueprint $table) {
            $table->string('title', 100)->nullable()->after('id');
        });

        // Step 2: Populate default titles for existing news
        DB::table('news')->orderBy('id')->chunk(100, function ($newsItems) {
            foreach ($newsItems as $news) {
                $date = date('d/m/Y', strtotime($news->created_at));
                DB::table('news')
                    ->where('id', $news->id)
                    ->update(['title' => "News del {$date}"]);
            }
        });

        // Step 3: Make title NOT NULL and modify text to longText
        Schema::table('news', function (Blueprint $table) {
            $table->string('title', 100)->nullable(false)->change();
            $table->longText('text')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            // Remove title column
            $table->dropColumn('title');

            // Revert text back to text(500)
            $table->text('text', 500)->change();
        });
    }
};