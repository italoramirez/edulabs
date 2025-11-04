<?php

namespace App\Repository\Auth;

interface AuthRepository
{
    /**
     * @param array $data
     * @return mixed
     */
    function register(array $data): mixed;

    /**
     * @param array $data
     * @return mixed
     */
    function login(array $data): mixed;

    /**
     * @param $user
     * @return void
     */
    function logout($user): void;
}
