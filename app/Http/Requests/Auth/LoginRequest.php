<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->credentials(), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Resolve the login identifier to the credentials Laravel should check.
     * Cashiers can use their username or POS staff number. Everyone can still
     * use their account email address.
     *
     * @return array{email?: string|null, username?: string, password: string}
     */
    private function credentials(): array
    {
        $identifier = trim((string) $this->input('email'));

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return [
                'email' => $identifier,
                'password' => (string) $this->input('password'),
            ];
        }

        $cashierStaffEmail = DB::table('pos_staff')
            ->join('users', 'users.id', '=', 'pos_staff.user_id')
            ->where('users.role', 'cashier')
            ->where(function ($query) use ($identifier) {
                $query->where('pos_staff.username', $identifier)
                    ->orWhere('pos_staff.number', $identifier);
            })
            ->select('users.email', 'users.username')
            ->first();

        if ($cashierStaffEmail) {
            return [
                $cashierStaffEmail->email ? 'email' : 'username' => $cashierStaffEmail->email ?: $cashierStaffEmail->username,
                'password' => (string) $this->input('password'),
            ];
        }

        $cashierAccount = DB::table('users')
            ->where('role', 'cashier')
            ->get(['email', 'username', 'name'])
            ->first(function ($user) use ($identifier) {
                $normalized = Str::lower($identifier);
                $email = Str::lower((string) $user->email);
                $username = Str::lower((string) $user->username);
                $name = Str::lower((string) $user->name);
                $emailUsername = Str::before($email, '@');

                return $normalized === $username || $normalized === $emailUsername || $normalized === $name;
            });

        if ($cashierAccount) {
            return [
                $cashierAccount->email ? 'email' : 'username' => $cashierAccount->email ?: $cashierAccount->username,
                'password' => (string) $this->input('password'),
            ];
        }

        return [
            'email' => $identifier,
            'password' => (string) $this->input('password'),
        ];
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}
