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
        Schema::table('services', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->change();
        });

        DB::statement('ALTER TABLE services ALTER COLUMN amount SET DEFAULT 0.00');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {

            $table->string('balance')->change();

            DB::statement('ALTER TABLE services ALTER COLUMN amount DROP DEFAULT');
        });
    }
};
