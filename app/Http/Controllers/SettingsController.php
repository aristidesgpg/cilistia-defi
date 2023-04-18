<?php

namespace App\Http\Controllers;

use App\Helpers\Settings;

class SettingsController extends Controller
{
    /**
     * Get brand settings
     *
     * @return array
     */
    public function getBrand(Settings $settings)
    {
        return $settings->brand->all();
    }

    /**
     * Get theme settings
     *
     * @return array
     */
    public function getTheme(Settings $settings)
    {
        return $settings->theme->all();
    }
}
