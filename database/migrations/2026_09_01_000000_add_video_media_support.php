<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            if (! Schema::hasColumn('product_images', 'media_type')) {
                $table->string('media_type', 20)->default('image')->after('path');
            }
        });

        Schema::table('home_slides', function (Blueprint $table) {
            if (! Schema::hasColumn('home_slides', 'video_path')) {
                $table->string('video_path')->nullable()->after('picture');
            }
        });
    }

    public function down(): void
    {
        Schema::table('home_slides', function (Blueprint $table) {
            if (Schema::hasColumn('home_slides', 'video_path')) {
                $table->dropColumn('video_path');
            }
        });

        Schema::table('product_images', function (Blueprint $table) {
            if (Schema::hasColumn('product_images', 'media_type')) {
                $table->dropColumn('media_type');
            }
        });
    }
};
