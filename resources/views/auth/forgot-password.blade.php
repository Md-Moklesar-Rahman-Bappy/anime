<x-guest-layout>
    <div class="min-vh-100 d-flex align-items-center justify-content-center" style="background: #0b0e16;">

        <div class="card" style="background: #111827; border-color: #1f2937; max-width: 28rem; width: 100%;">
            <div class="card-body p-4">

                <div class="text-center mb-4">
                    <h3 class="fw-bold" style="color: #fff;">
                        Ani<span style="color: #6366f1;">Stream</span>
                    </h3>
                    <p class="small mt-2" style="color: #9ca3af;">
                        Reset your password
                    </p>
                </div>

                <div class="mb-3 text-center" style="color: #9ca3af; font-size: 0.875rem;">
                    Forgot your password? Enter your email and we'll send you a reset link.
                </div>

                <x-auth-session-status
                    class="mb-3 text-center"
                    :status="session('status')"
                />

                <form method="POST" action="{{ route('auth.password.email') }}">
                    @csrf

                    <div class="mb-3">
                        <x-input-label for="email" value="Email" class="form-label" style="color: #d1d5db;" />

                        <x-text-input
                            id="email"
                            name="email"
                            type="email"
                            :value="old('email')"
                            required
                            autofocus
                            class="form-control"
                            style="background: #1f2937; border-color: #374151; color: #fff;"
                        />

                        <x-input-error
                            :messages="$errors->get('email')"
                            style="color: #f87171;"
                        />
                    </div>

                    <button
                        type="submit"
                        class="btn w-100 fw-semibold"
                        style="background: #4f46e5; border-color: #4f46e5; color: #fff;">
                        Send Reset Link
                    </button>

                    <div class="text-center mt-4" style="color: #9ca3af; font-size: 0.875rem;">
                        Remember your password?
                        <a href="{{ route('auth.login') }}"
                           style="color: #818cf8; text-decoration: none;">
                            Login
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
