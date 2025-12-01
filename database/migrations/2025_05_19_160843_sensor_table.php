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

        Schema::create('tracking_interests', function (Blueprint $table) {
            $table->id();
            $table->string('interest', 100);
            $table->boolean('is_active')->nullable()->default(false);
            $table->timestamps();
        });

        Schema::create('web_domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->unique();
            $table->string('country', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('shop_type', 20);
            $table->string('company_name', 255)->unique();
            $table->string('email', 255)->nullable();
            $table->string('phone_number', 30)->nullable();
            $table->string('identification_number', 50);
            $table->string('address', 255)->nullable();
            $table->string('notes', 255)->nullable();
            $table->boolean('is_reported')->default(false);
            $table->timestamp('reported_at')->nullable();
            $table->timestamps();
        });

        Schema::create('shop_domain', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('web_domain_id')->constrained('web_domains')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('found_on_domain_id')->references('id')->on('web_domains')->onDelete('cascade');
            $table->string('name', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone_number', 30)->nullable();
            $table->string('identification_number', 50)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('notes', 255)->nullable();
            $table->foreignId('affiliated_with_seller_id')->nullable()->references('id')->on('sellers')->onDelete('cascade');
            $table->boolean('is_reported')->default(false);
            $table->timestamp('reported_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->string('whitelist_class', 30)->default('Unknown');
            $table->boolean('currently_sells')->default(true);
            $table->boolean('is_selling_page')->default(false);
            $table->foreignId('seller_id')->nullable()->references('id')->on('sellers')->onDelete('cascade');
            $table->foreignId('redirects_to_page_id')->nullable()->references('id')->on('pages')->onDelete('cascade');
            $table->string('page_url', 500)->unique();
            $table->string('notes', 255)->nullable();
            $table->boolean('is_reported')->default(false);
            $table->timestamp('reported_at')->nullable();
            $table->timestamps();
        });


        Schema::create('search_query_strings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tracking_interest_id')->references('id')->on('tracking_interests')->onDelete('cascade');
            $table->string('search_intent', 20)->nullable();
            $table->string('query_string', 255);
            $table->string('language_code', 2);
            $table->string('source', 20)->nullable();
            $table->timestamps();

            $table->unique(['tracking_interest_id', 'query_string', 'language_code'], $name="unique_ti_query_lang");
        });

        Schema::create('pages_found', function(Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->foreignId('search_query_string_id')->references('id')->on('search_query_strings')->onDelete('cascade');
            $table->foreignId('tracking_interest_id')->references('id')->on('tracking_interests')->onDelete('cascade');
            $table->string('search_platform', 100);
            $table->boolean('serp_ads')->nullable()->default(false);
            $table->integer('serp_position')->unsigned();
            $table->timestamps();
        });

        Schema::create('sqs_search_volume', function (Blueprint $table) {
            $table->id();
            $table->foreignId('search_query_string_id')->references('id')->on('search_query_strings')->onDelete('cascade');
            $table->integer('volume')->unsigned();
            $table->timestamp('from_date');
            $table->timestamp('to_date');
            $table->string('data_source', 50)->nullable();
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('ticket_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->timestamp('price_at');
            $table->string('currency', 20)->nullable();
            $table->string('ticket_type', 20)->nullable();
            $table->decimal('selling_price', 19, 4)->nullable();
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->text('text', 500);
            $table->foreignId('for_user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('for_tracking_interest_id')->nullable()->constrained('tracking_interests')->cascadeOnDelete();
            $table->foreignId('added_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
        Schema::dropIfExists('pages_found');
        Schema::dropIfExists('shop_domain');
        Schema::dropIfExists('sqs_search_volume');
        Schema::dropIfExists('search_query_strings');
        Schema::dropIfExists('ticket_info');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('sellers');
        Schema::dropIfExists('shops');
        Schema::dropIfExists('web_domains');
        Schema::dropIfExists('tracking_interests');
    }
};