<?php

namespace App\Services\Settings\Impl;

use App\Repository\Setting\SettingsRepository;
use App\Services\Settings\SettingsService;

class SettingsServiceImpl implements SettingsService
{

    public function __construct(
        protected SettingsRepository $settingsRepository
    )
    {
    }

    /**
     * @param array $data
     * @return void
     */
    public function update(array $data): void
    {
        if (isset($data['default_limit'])) {
            $this->settingsRepository->update('default_limit', (int) $data['default_limit']);
        }

        if (isset($data['forbidden_extensions'])) {
            $this->settingsRepository->update('forbidden_extensions', $data['forbidden_extensions']);
        }
    }
}
