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
        Schema::table('services', function (Blueprint $table) {
            $table->enum('category', [
                'Upgrades',
                'Verifications',
                'Airtime',
                'Data',
                'A2C',
                'Electricity',
                'Cable Sub',
                'EPIN',
                'Agency',
                'Charges',
            ])->default('Verifications')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->enum('category', [
                'Upgrades',
                'Verifications',
                'Airtime',
                'Data',
                'A2C',
                'Electricity',
                'Cable Sub',
                'EPIN',
                'Agency',
            ])->default('Verifications')->change();
        });
    }
};
