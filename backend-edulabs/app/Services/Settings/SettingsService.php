<?php

namespace App\Services\Settings;

interface SettingsService
{
    /**
     * @param array $data
     * @return void
     */
    function update(array $data): void;
}
