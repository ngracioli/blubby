<?php

namespace App\Domain\Auth\Services;

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

final class MobileSessionAuthenticator
{
    public function authenticate(PersonalAccessToken $token, bool $nativeValid): bool
    {
        if (! $nativeValid || ! $token->tokenable instanceof User) {
            return false;
        }

        return $token->getConnection()->transaction(function () use ($token): bool {
            $persisted = $token->newQuery()->lockForUpdate()->find($token->getKey());
            $now = now()->startOfSecond();

            if (! $persisted || ! $persisted->expires_at || ! $persisted->expires_at->gt($now)
                || ! hash_equals($persisted->token, $token->token)
                || $persisted->tokenable_type !== $token->tokenable_type
                || (string) $persisted->tokenable_id !== (string) $token->tokenable_id) {
                return false;
            }

            $persisted->forceFill([
                'last_used_at' => $persisted->last_used_at?->max($now) ?? $now,
                'expires_at' => $persisted->expires_at->max($now->copy()->addSeconds(config('auth.mobile.inactivity_seconds'))),
            ])->save();
            $token->setRawAttributes($persisted->getAttributes(), true);

            return true;
        });
    }
}
