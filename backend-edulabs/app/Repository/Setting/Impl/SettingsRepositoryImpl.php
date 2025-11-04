<?php

namespace App\Repository\Setting\Impl;

use App\Models\Setting;
use App\Repository\Setting\SettingsRepository;
use PhpParser\Builder\Class_;

class SettingsRepositoryImpl implements SettingsRepository
{
    /**
     * @param string $key
     * @param $value
     * @return Setting
     */
    public function update(string $key, $value): Setting
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        return Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
