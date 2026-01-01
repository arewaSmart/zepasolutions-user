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
        Schema::create('user_enrollments', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->nullable();
            $table->string('bvn')->nullable();
            $table->string('agt_mgt_inst_name')->nullable();
            $table->string('agt_mgt_inst_code')->nullable();
            $table->string('agent_name')->nullable();
            $table->string('agent_code')->nullable();
            $table->string('enroller_code')->nullable();
            $table->text('latitude')->nullable();
            $table->text('longitude')->nullable();
            $table->string('finger_print_scanner')->nullable();
            $table->string('bms_import_id')->nullable();
            $table->string('validation_status')->nullable();
            $table->text('validation_message')->nullable();
            $table->string('amount')->nullable();
            $table->text('capture_date')->nullable();
            $table->text('sync_date')->nullable();
            $table->text('validation_date')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_enrollments');
    }
};
