<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_settings', function (Blueprint $table) {
            $table->id();
            
            // Header Section
            $table->string('header_title_line1')->default('Say hello to us');
            $table->string('header_title_line2')->default('love to hear you');
            $table->string('header_image')->nullable();
            
            // Contact Info
            $table->string('business_number')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('whatsapp_link')->nullable();
            $table->string('office_address')->nullable();
            
            // Social Media Links
            $table->string('facebook_link')->nullable();
            $table->string('instagram_link')->nullable();
            $table->string('twitter_link')->nullable();
            $table->string('youtube_link')->nullable();
            $table->string('linkedin_link')->nullable();
            $table->string('messenger_link')->nullable();
            $table->string('skype_link')->nullable();
            $table->string('tiktok_link')->nullable();
            
            // Contact Form Settings
            $table->string('form_email')->nullable(); // Email where contact forms are sent
            $table->boolean('form_enabled')->default(true);
            
            // Map/Location (optional)
            $table->string('map_embed_url')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_settings');
    }
};