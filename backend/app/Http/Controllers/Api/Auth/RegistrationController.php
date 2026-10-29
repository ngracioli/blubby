<?php

namespace App\Http\Controllers\Api\Auth;

use App\Domain\Auth\Actions\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Resources\Api\Auth\UserResource;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

final class RegistrationController extends Controller
{
    public function __invoke(RegisterRequest $request, RegisterUserAction $register): JsonResponse
    {
        $input = $request->validated();

        try {
            $result = $register->handle($input['name'], $input['email'], $input['password'], $input['device_name']);
        } catch (UniqueConstraintViolationException $exception) {
            if (! str_contains($exception->getMessage(), 'users_email_normalized_unique') && ! str_contains($exception->getMessage(), 'users_email_unique')) {
                throw $exception;
            }

            throw ValidationException::withMessages(['email' => 'Este e-mail não pode ser utilizado.']);
        }

        return response()->json(['data' => [
            'user' => (new UserResource($result['user']))->resolve($request),
            'token' => $result['token']->plainTextToken,
            'token_type' => 'Bearer',
        ]], 201)->header('Cache-Control', 'no-store');
    }
}
