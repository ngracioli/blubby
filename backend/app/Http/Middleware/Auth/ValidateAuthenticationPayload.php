<?php

namespace App\Http\Middleware\Auth;

use Closure;
use Illuminate\Http\Request;
use JsonException;
use Symfony\Component\HttpFoundation\Response;

final class ValidateAuthenticationPayload
{
    public function handle(Request $request, Closure $next): Response
    {
        if (strlen($request->getContent()) > 16384) {
            return response()->json(['message' => 'O conteúdo excede o tamanho permitido.'], 413)->header('Cache-Control', 'no-store');
        }

        try {
            $input = json_decode($request->getContent(), true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return response()->json(['message' => 'Conteúdo JSON inválido.'], 400)->header('Cache-Control', 'no-store');
        }

        if (! $request->isJson() || ! is_array($input)) {
            return response()->json(['message' => 'Conteúdo JSON inválido.'], 400)->header('Cache-Control', 'no-store');
        }

        return $next($request)->header('Cache-Control', 'no-store');
    }
}
