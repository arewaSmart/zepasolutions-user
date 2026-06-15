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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('transaction_ref')->nullable()->after('referenceId');
            $table->text('description')->nullable()->after('service_description');
            $table->string('performed_by')->nullable()->after('payer_email');
            $table->json('metadata')->nullable()->after('performed_by');
            
            // Change status from enum to string to support 'completed' and other values
            $table->string('status')->default('Pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['transaction_ref', 'description', 'performed_by', 'metadata']);
            
            // Revert status to enum
            $table->enum('status', ['Approved', 'Pending', 'Rejected'])->default('Pending')->change();
        });
    }
};
