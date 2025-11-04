<?php

namespace App\Services\Auth\Impl;

use App\Repository\Auth\AuthRepository;
use App\Services\Auth\AuthService;

class AuthServiceImpl implements AuthService
{

    public function __construct(
        protected AuthRepository $authRepository
    )
    {
    }

    /**
     * @param array $data
     * @return mixed
     */
    function register(array $data): mixed
    {
        return $this->authRepository->register($data);
    }

    /**
     * @param array $data
     * @return mixed
     */
    function login(array $data): mixed
    {
        return $this->authRepository->login($data);
    }

    function logout(): void
    {
        $model = auth()->user();
        $this->authRepository->logout($model);
    }
}
