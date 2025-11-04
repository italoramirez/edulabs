<?php

namespace App\Services\Settings;

use App\Models\FileUpload;
use App\Models\Setting;
use App\Models\User;

class StorageLimitServiceImpl implements StorageLimitService
{

    /**
     * @param User $user
     * @return int
     */
    public function getUserLimit(User $user): int
    {
        if ($user->storage_limit) {
            return (int) $user->storage_limit;
        }

        if ($user->group && $user->group->storage_limit) {
            return (int) $user->group->storage_limit;
        }

        return (int) Setting::where('key', 'default_limit')->value('value') ?? (10 * 1024 * 1024);
    }

    /**
     * @param User $user
     * @return int
     */
    public function getUserUsage(User $user): int
    {
        return (int) FileUpload::where('user_id', $user->id)->sum('size');
    }

    /**
     * @param User $user
     * @param int $fileSize
     * @return bool
     */
    public function canUpload(User $user, int $fileSize): bool
    {
        $limit = $this->getUserLimit($user);
        $used = $this->getUserUsage($user);
        return ($used + $fileSize) <= $limit;
    }
}
