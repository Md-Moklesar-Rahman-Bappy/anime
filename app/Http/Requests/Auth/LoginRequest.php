<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | AUTHORIZE
    |--------------------------------------------------------------------------
    */
    public function authorize(): bool
    {
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | RULES
    |--------------------------------------------------------------------------
    */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | NORMALIZE INPUT
    |--------------------------------------------------------------------------
    */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATE
    |--------------------------------------------------------------------------
    */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        try {
            if (!Auth::attempt(
                $this->only('email', 'password'),
                $this->boolean('remember')
            )) {

                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'email' => trans('auth.failed'),
                ]);
            }

            RateLimiter::clear($this->throttleKey());

        } catch (ValidationException $e) {
            throw $e;

        } catch (\Throwable $e) {

            $this->logError('Login authentication error', $e, [
                'email' => $this->input('email'),
                'ip' => $this->ip(),
            ]);

            throw ValidationException::withMessages([
                'email' => 'Authentication failed. Please try again.',
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RATE LIMIT CHECK
    |--------------------------------------------------------------------------
    */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        logger()->warning('Login rate limit triggered', [
            'email' => $this->input('email'),
            'ip' => $this->ip(),
            'seconds' => $seconds,
        ]);

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | THROTTLE KEY
    |--------------------------------------------------------------------------
    */
    public function throttleKey(): string
    {
        return Str::lower($this->string('email')) . '|' . $this->ip();
    }

    /*
    |--------------------------------------------------------------------------
    | LOG HELPER
    |--------------------------------------------------------------------------
    */
    protected function logError(string $message, \Throwable $e, array $context = []): void
    {
        logger()->error($message, array_merge($context, [
            'error' => $e->getMessage(),
        ]));
    }
}