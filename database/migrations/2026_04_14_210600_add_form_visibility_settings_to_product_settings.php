<?php

use App\Models\Settings;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $settings = Settings::where('key', 'product')->first();
        if ($settings) {
            $value = (array) $settings->value;
            $newSettings = [
                'price_label_status' => 1,
                'additional_features_status' => 1,
                'custom_services_status' => 1,
                'terms_conditions_status' => 1,
            ];
            $settings->value = array_merge($value, $newSettings);
            $settings->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $settings = Settings::where('key', 'product')->first();
        if ($settings) {
            $value = (array) $settings->value;
            unset($value['price_label_status']);
            unset($value['additional_features_status']);
            unset($value['custom_services_status']);
            unset($value['terms_conditions_status']);
            $settings->value = $value;
            $settings->save();
        }
    }
};
