<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Helpers\ServiceManager;

class BvnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Keystone Bank Services (User request: Keyston Bank)
        ServiceManager::getServiceWithFields('Keyston Bank', [
            ['name' => 'BVN Deletion Request', 'code' => '67', 'price' => 20000],
            ['name' => 'Date of birth update', 'code' => '68', 'price' => 8000],
            ['name' => 'Correction of name', 'code' => '69', 'price' => 15000],
            ['name' => 'Phone number and email update', 'code' => '70', 'price' => 8000],
            ['name' => 'Date of birth and name update', 'code' => '71', 'price' => 20000],
            ['name' => 'Gender Update', 'code' => '72', 'price' => 5000],
            ['name' => 'BVN Revalidation', 'code' => '73', 'price' => 8000],
        ]);

        // 2. Bank of Agriculture Services
        ServiceManager::getServiceWithFields('BANK OF AGRICULTURE', [
            ['name' => 'Date of birth update', 'code' => '41', 'price' => 7000],
            ['name' => 'Correction of name', 'code' => '42', 'price' => 7000],
            ['name' => 'Phone number and email update', 'code' => '43', 'price' => 7000],
            ['name' => 'Date of birth and name update', 'code' => '44', 'price' => 10000],
            ['name' => 'Gender Update', 'code' => '46', 'price' => 7000],
            ['name' => 'BVN Revalidation', 'code' => '47', 'price' => 5000],
            ['name' => 'BVN Deletion Request', 'code' => '62', 'price' => 20000],
        ]);

        // 3. Heritage Bank Services
        ServiceManager::getServiceWithFields('HERITAGE BANK', [
            ['name' => 'Date of birth update', 'code' => '48', 'price' => 7000],
            ['name' => 'Correction of name', 'code' => '49', 'price' => 7000],
            ['name' => 'Phone number and email update', 'code' => '50', 'price' => 7000],
            ['name' => 'Date of birth and name update', 'code' => '51', 'price' => 10000],
            ['name' => 'Gender Update', 'code' => '52', 'price' => 5000],
            ['name' => 'BVN Revalidation', 'code' => '53', 'price' => 10000],
            ['name' => 'Change of address', 'code' => '54', 'price' => 10000],
            ['name' => 'BVN Deletion Request', 'code' => '63', 'price' => 20000],
        ]);

        // 4. BVN SEARCH Service
        ServiceManager::getServiceWithFields('BVN SEARCH', [
            ['name' => 'Search BVN', 'code' => '45', 'price' => 1300],
        ]);

        // 5. FIRST BANK Services
        ServiceManager::getServiceWithFields('FIRST BANK', [
            ['name' => 'Correction of name', 'code' => '003', 'price' => 12000],
            ['name' => 'Date of birth update', 'code' => '004', 'price' => 10000],
            ['name' => 'Phone Number Update', 'code' => '005', 'price' => 7000],
            ['name' => 'Correction of name and date of birth', 'code' => '006', 'price' => 15000],
            ['name' => 'Complete change of name', 'code' => '007', 'price' => 50000],
            ['name' => 'Gender Update', 'code' => '008', 'price' => 5000],
            ['name' => 'Bvn Revalidation', 'code' => '009', 'price' => 10000],
            ['name' => 'Whitelist BVN', 'code' => '010', 'price' => 15000],
            ['name' => 'BVN Deletion Request', 'code' => '060', 'price' => 20000],
            ['name' => 'Correction of Name, DOB and phone NO', 'code' => '050', 'price' => 18000],
        ]);

        // 6. CRM Services
        ServiceManager::getServiceWithFields('CRM', [
            ['name' => 'Central Risk Management', 'code' => '021', 'price' => 1500],
        ]);

        // 7. AGENCY BANKING Services
        ServiceManager::getServiceWithFields('AGENCY BANKING', [
            ['name' => 'Correction of name', 'code' => '022', 'price' => 6500],
            ['name' => 'Date of birth update', 'code' => '023', 'price' => 6000],
            ['name' => 'Correction of name and date of birth', 'code' => '024', 'price' => 8000],
            ['name' => 'Phone Number Update', 'code' => '025', 'price' => 5000],
            ['name' => 'Gender Update', 'code' => '026', 'price' => 6000],
            ['name' => 'Bvn Revalidation', 'code' => '027', 'price' => 10000],
            ['name' => 'BVN full Alienment With ID', 'code' => '028', 'price' => 10000],
            ['name' => 'BVN Deletion Request', 'code' => '66', 'price' => 20000],
            ['name' => 'Complete change of name', 'code' => 'A109', 'price' => 60000],
        ]);

        // 8. NIBSS BANKING Services
        ServiceManager::getServiceWithFields('NIBSS BANKING', [
            ['name' => 'Date of birth update', 'code' => '55', 'price' => 7000],
            ['name' => 'Correction of name', 'code' => '56', 'price' => 7000],
            ['name' => 'Phone number and email update', 'code' => '57', 'price' => 7000],
            ['name' => 'Date of birth and name update', 'code' => '58', 'price' => 10000],
            ['name' => 'Gender Update', 'code' => '59', 'price' => 7000],
            ['name' => 'BVN Revalidation', 'code' => '60', 'price' => 5000],
            ['name' => 'BVN Deletion Request', 'code' => '61', 'price' => 20000],
        ]);

        // 9. Affidavit Services
        ServiceManager::getServiceWithFields('AFFIDAVIT', [
            ['name' => 'Affidavit', 'code' => '029', 'price' => 500],
        ]);
    }
}
