<?php

namespace Pkaratanev\NovaSettingsTool\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Pkaratanev\NovaSettingsTool\Traits\Settings;
use Pkaratanev\NovaSettingsTool\ValueObjects\SettingRegister;
use Illuminate\Http\JsonResponse;

/**
 * Class SettingsController
 * @package Pkaratanev\NovaSettingsTool\Http\Controllers
 */
final class SettingsController
{
    /**
     * Get settings.
     * @return JsonResponse
     */
    public function get(): JsonResponse
    {
        return response()->json(SettingRegister::getInstance());
    }

    /**
     * Check if the database migrations are migrated.
     * @return JsonResponse
     */
    public function installed(): JsonResponse
    {
        return response()->json(['installed' => Schema::hasTable('settings')]);
    }

    /**
     * Update settings.
     * @param Request $request
     * @return JsonResponse
     */
    public function process(Request $request): JsonResponse
    {
        $values = $request->get('values');

        if (is_null($values)) {
            $values = [];
        }

        $settingRegister = SettingRegister::getInstance();

        $settingRegister->massUpdate($values);

        return response()->json([
            'settings' => $settingRegister,
            'message' => __('settings::settings.save_success'),
        ]);
    }
}
