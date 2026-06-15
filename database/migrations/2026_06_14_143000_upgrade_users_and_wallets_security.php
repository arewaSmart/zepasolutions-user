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
        // 1. Upgrade wallets table
        Schema::table('wallets', function (Blueprint $table) {
            // Rename balance to hold_balance
            $table->renameColumn('balance', 'hold_balance');
        });

        Schema::table('wallets', function (Blueprint $table) {
            // Create a new balance column
            $table->decimal('balance', 10, 2)->default(0.00)->after('user_id');
            
            // Alter user_id to be unsigned big integer to support foreign key
            $table->unsignedBigInteger('user_id')->change();
        });

        // Add foreign key constraint to wallets.user_id referencing users.id
        Schema::table('wallets', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Add CHECK constraints to prevent negative balances
        DB::statement('ALTER TABLE wallets ADD CONSTRAINT check_wallet_balance_positive CHECK (balance >= 0)');
        DB::statement('ALTER TABLE wallets ADD CONSTRAINT check_wallet_hold_balance_positive CHECK (hold_balance >= 0)');
        DB::statement('ALTER TABLE wallets ADD CONSTRAINT check_wallet_deposit_positive CHECK (deposit >= 0)');

        // 2. Upgrade users table security by adding indexes to frequently searched fields
        Schema::table('users', function (Blueprint $table) {
            $table->index('username');
            $table->index('phone_number');
            $table->index('kyc_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove CHECK constraints
        DB::statement('ALTER TABLE wallets DROP CONSTRAINT check_wallet_balance_positive');
        DB::statement('ALTER TABLE wallets DROP CONSTRAINT check_wallet_hold_balance_positive');
        DB::statement('ALTER TABLE wallets DROP CONSTRAINT check_wallet_deposit_positive');

        Schema::table('wallets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn('balance');
        });

        Schema::table('wallets', function (Blueprint $table) {
            $table->renameColumn('hold_balance', 'balance');
            $table->bigInteger('user_id')->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['username']);
            $table->dropIndex(['phone_number']);
            $table->dropIndex(['kyc_status']);
        });
    }
};
