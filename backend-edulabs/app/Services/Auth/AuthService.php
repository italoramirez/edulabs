<?php

namespace App\Services\Auth;

interface AuthService
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
     * @return void
     */
    function logout(): void;
}
