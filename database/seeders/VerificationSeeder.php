<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Helpers\ServiceManager;

class VerificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Copy placeholder image to public/assets/images/corrupt.jpg if not exists
        $source = "C:\\Users\\shafi\\.gemini\\antigravity\\brain\\3f297c46-1b2e-4d2c-a027-6dfb062edc5d\\corrupt_1781440303050.png";
        $dest = public_path('assets/images/corrupt.jpg');
        
        if (file_exists($source)) {
            // Ensure target directory exists
            $dir = dirname($dest);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            copy($source, $dest);
        }

        // 2. Seed Verification Service and its Fields
        ServiceManager::getServiceWithFields('Verification', [
            // BVN Fields
            ['name' => 'Bvn verification', 'code' => '600', 'price' => 70],
            ['name' => 'standard slip', 'code' => '601', 'price' => 50],
            ['name' => 'preminum slip', 'code' => '602', 'price' => 100],
            ['name' => 'plastic slip', 'code' => '603', 'price' => 150],

            // NIN Fields
            ['name' => 'Verify NIN', 'code' => '610', 'price' => 80],
            ['name' => 'Regular Slip', 'code' => 'V102', 'price' => 100],
            ['name' => 'standard slip', 'code' => '611', 'price' => 100],
            ['name' => 'preminum slip', 'code' => '612', 'price' => 150],
            ['name' => '1Vnin slip', 'code' => '616', 'price' => 100],

            // Phone Fields
            ['name' => 'Phone NIN Verification', 'code' => 'V105', 'price' => 100],

            // Demo Fields
            ['name' => 'Demo Verification', 'code' => 'V100', 'price' => 100],
            ['name' => 'Free Slip', 'code' => 'V101', 'price' => 0],
        ]);
    }
}
