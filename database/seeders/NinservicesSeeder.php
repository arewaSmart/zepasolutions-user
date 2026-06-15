<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Helpers\ServiceManager;

class NinservicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed the 'Validation' service with its fields
        ServiceManager::getServiceWithFields('Validation', [
            ['name' => 'NIN Validation', 'code' => '015', 'price' => 100],
        ]);

        // Seed the 'IPE' service with its fields
        ServiceManager::getServiceWithFields('IPE', [
            ['name' => 'IPE Clearance', 'code' => '002', 'price' => 100],
        ]);

        // Seed the 'NIN Modification' service with its fields
        ServiceManager::getServiceWithFields('NIN Modification', [
            ['name' => 'Correction of name', 'code' => '032', 'price' => 8000],
            ['name' => 'Phone Number Update', 'code' => '033', 'price' => 8000],
            ['name' => 'Gender Update', 'code' => '034', 'price' => 30000],
            ['name' => 'Date of birth update below 5 year', 'code' => '035', 'price' => 45000],
            ['name' => 'Date of birth Update above 5 year', 'code' => '036', 'price' => 120000],
            ['name' => 'Change of Ressidence Address', 'code' => '037', 'price' => 8000],
            ['name' => 'Change of state of Origin', 'code' => '0141', 'price' => 35000],
        ]);
    }
}
