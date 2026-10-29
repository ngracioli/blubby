<?php

namespace App\Domain\Auth\Actions;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\NewAccessToken;

final class AuthenticateUserAction
{
    /** @return array{user: User, token: NewAccessToken} */
    public function handle(string $email, string $password, string $deviceName): array
    {
        $user = User::withEmailIdentity($email)->first();
        $valid = Hash::check($password, $user?->password ?? config('auth.mobile.dummy_password_hash'));

        if (! $user || ! $valid) {
            throw new AuthenticationException('E-mail ou senha inválidos.');
        }

        return DB::transaction(function () use ($user, $password, $deviceName): array {
            if (Hash::needsRehash($user->password)) {
                $user->password = Hash::make($password);
                $user->save();
            }

            $token = $user->createToken($deviceName, ['user:read', 'session:revoke'], now()->startOfSecond()->addSeconds(config('auth.mobile.inactivity_seconds')));

            return ['user' => $user, 'token' => $token];
        });
    }
}
