<?php

namespace App\Repository\Setting;

use App\Models\Setting;

interface SettingsRepository
{
    /**
     * @param string $key
     * @param $value
     * @return mixed
     */
    function update(string $key, $value): Setting;
}
