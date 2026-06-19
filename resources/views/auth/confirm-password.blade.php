<x-guest-layout>
    <div class="min-vh-100 d-flex align-items-center justify-content-center" style="background: #0b0e16;">

        <div class="card" style="background: #111827; border-color: #1f2937; max-width: 28rem; width: 100%;">
            <div class="card-body p-4">

                <div class="text-center mb-4">
                    <h3 class="fw-bold" style="color: #fff;">
                        Ani<span style="color: #6366f1;">Stream</span>
                    </h3>
                    <p class="small mt-2" style="color: #9ca3af;">
                        Confirm your password
                    </p>
                </div>

                <div class="mb-3 text-center" style="color: #9ca3af; font-size: 0.875rem;">
                    This is a secure area. Please confirm your password to continue.
                </div>

                <form method="POST" action="{{ route('auth.password.confirm') }}">
                    @csrf

                    <div class="mb-3">
                        <x-input-label for="password" value="Password" class="form-label" style="color: #d1d5db;" />

                        <x-text-input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autofocus
                            autocomplete="current-password"
                            class="form-control"
                            style="background: #1f2937; border-color: #374151; color: #fff;"
                        />

                        <x-input-error :messages="$errors->get('password')" style="color: #f87171;" />
                    </div>

                    <button
                        type="submit"
                        class="btn w-100 fw-semibold"
                        style="background: #4f46e5; border-color: #4f46e5; color: #fff;">
                        Confirm Password
                    </button>

                    <div class="text-center mt-4" style="color: #9ca3af; font-size: 0.875rem;">
                        Back to
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
