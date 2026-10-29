<?php

namespace App\Http\Middleware\Auth;

use App\Domain\Auth\Exceptions\AuthenticationAdmissionUnavailable;
use App\Domain\Auth\Services\AuthenticationAttemptLimiter;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

final class ThrottleAuthentication
{
    public function __construct(private readonly AuthenticationAttemptLimiter $limiter) {}

    public function handle(Request $request, Closure $next, string $operation): Response
    {
        try {
            $retry = $this->limiter->admit($operation, $this->signals($request));
        } catch (AuthenticationAdmissionUnavailable) {
            return response()->json(['message' => 'Autenticação temporariamente indisponível.'], 503)->header('Cache-Control', 'no-store');
        }

        if ($retry > 0) {
            return response()->json(['message' => 'Muitas tentativas. Tente novamente mais tarde.'], 429)
                ->header('Retry-After', (string) $retry)->header('Cache-Control', 'no-store');
        }

        return $next($request);
    }

    /** @return array<string, string> */
    private function signals(Request $request): array
    {
        $signals = [];
        $ip = $request->ip();
        $packed = is_string($ip) ? @inet_pton($ip) : false;
        $signals['ip'] = $packed === false ? 'unknown' : inet_ntop($packed);

        $device = $request->input('device_id');
        if (is_string($device) && preg_match('/\A[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\z/i', $device)) {
            $signals['device'] = strtolower($device);
        }

        $email = $request->input('email');
        if (is_string($email) && strlen($email) <= 254) {
            $email = User::normalizeEmail($email);
            // Same predicate as the Form Requests, so no validated address escapes the email budget.
            if (Validator::make(['email' => $email], ['email' => ['ascii', 'email:rfc']])->passes()) {
                $signals['email'] = $email;
            }
        }

        return $signals;
    }
}
