<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('referral_bonus', 10, 2)->change();
        });

        DB::statement('ALTER TABLE users ALTER COLUMN referral_bonus SET DEFAULT 0.00');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_bonus')->change();
        });

        DB::statement('ALTER TABLE users ALTER COLUMN referral_bonus DROP DEFAULT');
    }
};
