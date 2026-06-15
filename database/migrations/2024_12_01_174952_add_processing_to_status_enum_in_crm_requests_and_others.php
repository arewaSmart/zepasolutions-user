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
        // Add 'processing' to status enum in crm_requests
        Schema::table('crm_requests', function (Blueprint $table) {
            $table->enum('status', ['pending', 'resolved', 'rejected', 'processing'])->change();
        });

        // Add 'processing' to status enum in crm_requests2
        Schema::table('crm_requests2', function (Blueprint $table) {
            $table->enum('status', ['pending', 'resolved', 'rejected', 'processing'])->change();
        });

        // Add 'processing' to status enum in bvn_modifications
        Schema::table('bvn_modifications', function (Blueprint $table) {
            $table->enum('status', ['pending', 'resolved', 'rejected', 'processing'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_requests', function (Blueprint $table) {
            $table->enum('status', ['pending', 'resolved', 'rejected'])->change();
        });

        // Revert status enum to original in crm_requests2
        Schema::table('crm_requests2', function (Blueprint $table) {
            $table->enum('status', ['pending', 'resolved', 'rejected'])->change();
        });

        // Revert status enum to original in bvn_modifications
        Schema::table('bvn_modifications', function (Blueprint $table) {
            $table->enum('status', ['pending', 'resolved', 'rejected'])->change();
        });
    }
};
