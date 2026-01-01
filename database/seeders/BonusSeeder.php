<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class bonusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('referral_bonus')->updateOrInsert(
            ['id' => 1], // Ensure only one record for user_id = 1
            [
                'bonus' => 100,
                'updated_at' => Carbon::parse('2024-08-04 18:32:57'),
                'created_at' => Carbon::parse('2024-08-04 18:32:57'),
            ]
        );
    }
}
