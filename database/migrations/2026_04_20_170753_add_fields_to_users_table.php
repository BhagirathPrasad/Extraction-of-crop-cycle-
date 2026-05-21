<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('farmer'); // admin, researcher, farmer
            $table->string('locale')->default('en');
            $table->string('theme')->default('light');
            $table->string('avatar')->nullable();
            $table->string('phone')->nullable();
            $table->string('organization')->nullable();
            $table->string('region')->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_secret')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->boolean('is_active')->default(true);
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
