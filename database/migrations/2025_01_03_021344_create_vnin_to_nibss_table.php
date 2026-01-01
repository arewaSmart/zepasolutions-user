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
        Schema::create('vnin_to_nibss', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->bigInteger('tnx_id');
            $table->string('refno');
            $table->string('requestId');
            $table->string('nin_number');
            $table->string('bvn_number');
            $table->enum('status', ['resolved', 'pending', 'rejected', 'processing'])->default('pending');
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vnin_to_nibss');
    }
};
