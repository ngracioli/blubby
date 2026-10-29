<?php

namespace App\Http\Controllers\Api\Auth;

use App\Domain\Auth\Actions\AuthenticateUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Resources\Api\Auth\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class SessionController extends Controller
{
    public function store(LoginRequest $request, AuthenticateUserAction $authenticate): JsonResponse
    {
        $input = $request->validated();
        $result = $authenticate->handle($input['email'], $input['password'], $input['device_name']);

        return response()->json(['data' => [
            'user' => (new UserResource($result['user']))->resolve($request),
            'token' => $result['token']->plainTextToken,
            'token_type' => 'Bearer',
        ]])->header('Cache-Control', 'no-store');
    }

    public function destroy(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent()->header('Cache-Control', 'no-store');
    }
}
