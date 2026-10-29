<?php

namespace App\Domain\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\NewAccessToken;

final class RegisterUserAction
{
    /** @return array{user: User, token: NewAccessToken} */
    public function handle(string $name, string $email, string $password, string $deviceName): array
    {
        return DB::transaction(function () use ($name, $email, $password, $deviceName): array {
            $user = User::create(['name' => $name, 'email' => User::normalizeEmail($email), 'password' => $password]);
            $token = $user->createToken($deviceName, ['user:read', 'session:revoke'], now()->startOfSecond()->addSeconds(config('auth.mobile.inactivity_seconds')));

            return ['user' => $user, 'token' => $token];
        });
    }
}
