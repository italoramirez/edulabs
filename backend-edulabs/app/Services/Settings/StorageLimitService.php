<?php

namespace App\Services\Settings;

use App\Models\User;

interface StorageLimitService
{
    /**
     * @param User $user
     * @return int
     */
    public function getUserLimit(User $user): int;

    /**
     * @param User $user
     * @return mixed
     */
    public function getUserUsage(User $user): int;

    /**
     * @param User $user
     * @param int $fileSize
     * @return bool
     */
    public function canUpload(User $user, int $fileSize): bool;
}
