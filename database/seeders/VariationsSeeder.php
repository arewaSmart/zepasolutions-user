<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VariationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $token = env('AREWA_API_TOKEN');
        $baseUrl = env('AREWA_BASE_URL', 'https://api.arewasmart.com.ng/api/v1');

        if (!$token) {
            $this->command->error("AREWA_API_TOKEN is not configured in .env file.");
            return;
        }

        // 1. Fetch & Seed Regular Data Variations
        $this->command->info("Fetching regular data variations...");
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->get($baseUrl . '/data/variations');

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['data']) && is_array($data['data'])) {
                    $count = 0;
                    foreach ($data['data'] as $variation) {
                        $variationCode = $variation['variation_code'] ?? '';
                        if (!$variationCode) continue;

                        $parts = explode('-', $variationCode);
                        $network = $parts[0] ?? 'unknown';

                        DB::table('data_variations')->updateOrInsert(
                            ['variation_code' => $variationCode],
                            [
                                'service_name'    => $variation['service_name'] ?? (ucfirst($network) . ' Data'),
                                'service_id'      => $variation['service_id'] ?? ($network . '-data'),
                                'convinience_fee' => $variation['convinience_fee'] ?? 0,
                                'name'            => $variation['name'] ?? $variationCode,
                                'variation_amount'=> $variation['variation_amount'] ?? ($variation['price'] ?? 0),
                                'fixedPrice'      => $variation['fixedPrice'] ?? 'Yes',
                                'status'          => $variation['status'] ?? 'enabled',
                                'created_at'      => Carbon::now(),
                                'updated_at'      => Carbon::now()
                            ]
                        );
                        $count++;
                    }
                    $this->command->info("Successfully seeded {$count} regular data variations!");
                } else {
                    $this->command->error("Failed: API response did not contain 'data' key.");
                }
            } else {
                $this->command->error("Failed to fetch regular data: " . $response->status() . " - " . $response->body());
            }
        } catch (\Exception $e) {
            $this->command->error("Error seeding regular data: " . $e->getMessage());
        }

        // 2. Fetch & Seed SME Data Variations
        $this->command->info("Fetching SME data variations...");
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->get($baseUrl . '/sme-data/variations');

            if ($response->successful()) {
                $data = $response->json();
                // Depending on structure, it could be directly in $data['data'] or root array
                $items = $data['data'] ?? $data;

                if (is_array($items)) {
                    $count = 0;
                    foreach ($items as $item) {
                        $dataId = $item['data_id'] ?? null;
                        if (!$dataId) continue;

                        DB::table('sme_datas')->updateOrInsert(
                            ['data_id' => $dataId],
                            [
                                'network'   => $item['network'] ?? '',
                                'plan_type' => $item['plan_type'] ?? '',
                                'amount'    => $item['amount'] ?? 0,
                                'size'      => $item['size'] ?? '',
                                'validity'  => $item['validity'] ?? '',
                                'status'    => $item['status'] ?? 'enabled',
                                'created_at'=> Carbon::now(),
                                'updated_at'=> Carbon::now()
                            ]
                        );
                        $count++;
                    }
                    $this->command->info("Successfully seeded {$count} SME data variations!");
                } else {
                    $this->command->error("Failed: SME variations response is not an array.");
                }
            } else {
                $this->command->error("Failed to fetch SME data: " . $response->status() . " - " . $response->body());
            }
        } catch (\Exception $e) {
            $this->command->error("Error seeding SME data: " . $e->getMessage());
        }
    }
}
