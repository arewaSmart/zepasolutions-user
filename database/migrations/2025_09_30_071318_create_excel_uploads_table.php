<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExcelUploadsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('excel_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('record_number')->nullable();
            $table->string('beneficiary_account')->nullable();
            $table->string('beneficiary_bankcode')->nullable();
            $table->string('beneficiary_name')->nullable();
            $table->decimal('transaction_amount', 15, 2)->nullable(); // Supports large amounts
            $table->string('narration')->nullable();
            $table->string('new_account_name')->nullable();
            $table->enum('status', ['0', '1'])->default('0'); // Enum with '0' and '1'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('excel_uploads');
    }
}
