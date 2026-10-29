<?php

namespace App\Http\Requests\Api\Auth;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

final class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => User::normalizeEmail($this->input('email'))]);
        }
    }

    /** @return array<string, array<mixed>> */
    public function rules(): array
    {
        return [
            'email' => ['bail', 'required', 'string', 'ascii', 'max:254', 'email:rfc'],
            'password' => ['bail', 'required', 'string', function (string $attribute, string $value, Closure $fail): void {
                if (strlen($value) > 72 || str_contains($value, "\0")) {
                    $fail('A senha deve ter no máximo 72 bytes e não pode conter caracteres nulos.');
                }
            }],
            'device_name' => ['bail', 'required', 'string', 'max:100', 'not_regex:/[\p{C}]/u'],
            'device_id' => ['bail', 'required', 'string', 'size:36', 'uuid:4'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            '*.required' => 'O campo :attribute é obrigatório.',
            '*.string' => 'O campo :attribute deve ser um texto.',
            '*.max' => 'O campo :attribute excede o tamanho permitido.',
            '*.not_regex' => 'O campo :attribute contém caracteres inválidos.',
            'email.ascii' => 'Informe um e-mail válido com caracteres ASCII.',
            'email.email' => 'Informe um e-mail válido.',
            'device_id.size' => 'O identificador da instalação deve ser um UUID válido.',
            'device_id.uuid' => 'O identificador da instalação deve ser um UUID válido.',
        ];
    }
}
