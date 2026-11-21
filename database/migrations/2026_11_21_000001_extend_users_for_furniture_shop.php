<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('avatar', 500)->nullable()->after('phone');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('avatar');
            $table->date('birthday')->nullable()->after('gender');
            $table->string('role', 30)->default('customer')->after('birthday');
            $table->boolean('is_active')->default(true)->after('role');
            $table->string('lock_reason', 255)->nullable()->after('is_active');
            $table->dateTime('locked_at')->nullable()->after('lock_reason');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'phone',
                'avatar',
                'gender',
                'birthday',
                'role',
                'is_active',
                'lock_reason',
                'locked_at',
            ]);
        });
    }
};