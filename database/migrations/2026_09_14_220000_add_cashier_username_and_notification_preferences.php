<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->after('name');
            }

            if (! Schema::hasColumn('users', 'email_notifications_enabled')) {
                $table->boolean('email_notifications_enabled')->default(true)->after('is_active');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        Schema::table('pos_staff', function (Blueprint $table) {
            if (! Schema::hasColumn('pos_staff', 'username')) {
                $table->string('username')->nullable()->after('number');
            }

            $table->string('email')->nullable()->change();
        });

        $staffRows = DB::table('pos_staff')->orderBy('id')->get();
        foreach ($staffRows as $staff) {
            $username = self::uniqueUsername($staff->username ?: $staff->number ?: $staff->name ?: 'cashier');

            DB::table('pos_staff')->where('id', $staff->id)->update(['username' => $username]);

            if ($staff->user_id) {
                DB::table('users')->where('id', $staff->user_id)->update([
                    'username' => $username,
                    'email_notifications_enabled' => true,
                ]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
        });

        Schema::table('pos_staff', function (Blueprint $table) {
            $table->unique('username');
        });
    }

    public function down(): void
    {
        Schema::table('pos_staff', function (Blueprint $table) {
            if (Schema::hasColumn('pos_staff', 'username')) {
                $table->dropUnique(['username']);
                $table->dropColumn('username');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'username')) {
                $table->dropUnique(['username']);
                $table->dropColumn('username');
            }

            if (Schema::hasColumn('users', 'email_notifications_enabled')) {
                $table->dropColumn('email_notifications_enabled');
            }
        });
    }

    private static function uniqueUsername(string $value): string
    {
        $base = Str::of($value)->lower()->replaceMatches('/[^a-z0-9_.-]+/', '-')->trim('-')->limit(40, '')->toString() ?: 'cashier';
        $username = $base;
        $counter = 2;

        while (
            DB::table('users')->where('username', $username)->exists()
            || DB::table('pos_staff')->where('username', $username)->exists()
        ) {
            $username = Str::limit($base, 34, '') . '-' . $counter;
            $counter++;
        }

        return $username;
    }
};
