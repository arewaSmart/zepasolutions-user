<?php

namespace App\Helpers;

use App\Models\Service;
use App\Models\ServiceField;

class ServiceManager
{
    /**
     * Get or create service with fields and default prices.
     *
     * @param string $serviceName
     * @param array $fieldsData
     * @return \App\Models\Service
     */
    public static function getServiceWithFields(string $serviceName, array $fieldsData)
    {
        // 1. Get or create the service
        $service = Service::where('name', $serviceName)->first();
        if (!$service) {
            $service = Service::create([
                'name' => $serviceName,
                'description' => $serviceName . ' Services',
                'is_active' => true
            ]);
        }

        // 2. Loop through and get/create fields
        foreach ($fieldsData as $fieldData) {
            if (ServiceField::where('field_code', $fieldData['code'])->exists()) {
                continue;
            }

            ServiceField::create([
                'service_id' => $service->id,
                'field_code' => $fieldData['code'],
                'field_name' => $fieldData['name'],
                'base_price' => $fieldData['price'],
                'is_active' => true
            ]);
        }

        return $service;
    }
}
