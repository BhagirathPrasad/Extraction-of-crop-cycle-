<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('farmer')->after('email'); // admin, researcher, farmer
            $table->string('locale')->default('en')->after('role');
            $table->string('theme')->default('light')->after('locale');
            $table->string('avatar')->nullable()->after('theme');
            $table->string('phone')->nullable()->after('avatar');
            $table->string('organization')->nullable()->after('phone');
            $table->string('region')->nullable()->after('organization');
            $table->boolean('two_factor_enabled')->default(false)->after('region');
            $table->string('two_factor_secret')->nullable()->after('two_factor_enabled');
            $table->timestamp('last_login_at')->nullable()->after('two_factor_secret');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->boolean('is_active')->default(true)->after('last_login_ip');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role', 'locale', 'theme', 'avatar', 'phone',
                'organization', 'region', 'two_factor_enabled',
                'two_factor_secret', 'last_login_at', 'last_login_ip', 'is_active'
            ]);
        });
    }
};
