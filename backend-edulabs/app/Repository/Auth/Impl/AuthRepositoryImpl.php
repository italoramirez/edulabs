<?php

namespace App\Repository\Auth\Impl;

use App\Models\User;
use App\Repository\Auth\AuthRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthRepositoryImpl implements AuthRepository
{

    /**
     * @param array $data
     * @return mixed
     */
    function register(array $data): mixed
    {

        $user = User::create($data);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,

        ];
    }

    /**
     * @param array $data
     * @return mixed
     */
    function login(array $data): mixed
    {
        $user = $this->findByEmail($data['email']);

        if (!$user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales incorrectas.']
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        return [
            'token' => $token,
            'user' => $user,
        ];
    }

    /**
     * @param $user
     * @return void
     */
    function logout($user): void
    {
        $user->tokens()->delete();
    }

    /**
     * @param string $email
     * @return mixed
     */
    function findByEmail(string $email): mixed
    {
        return User::where('email', $email)->first();
    }
}
