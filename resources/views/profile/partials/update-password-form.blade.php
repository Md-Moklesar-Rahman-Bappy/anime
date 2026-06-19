<section class="d-flex flex-column gap-3">

    <header>
        <h2 class="fw-semibold" style="color:#fff;font-size:1.125rem">
            Update Password
        </h2>

        <p class="mt-1" style="color:#9ca3af;font-size:0.875rem">
            Use a strong, unique password to keep your account secure.
        </p>
    </header>

    <form method="POST" action="{{ route('auth.password.update') }}" class="d-flex flex-column gap-3">
        @csrf
        @method('PUT')

        <div>
            <label class="d-block mb-1" style="color:#9ca3af;font-size:0.875rem">Current Password</label>
            <input
                type="password"
                name="current_password"
                autocomplete="current-password"
                class="form-control"
                style="background:#1f2937;border-color:#374151;color:#fff"
            />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <label class="d-block mb-1" style="color:#9ca3af;font-size:0.875rem">New Password</label>
            <input
                type="password"
                name="password"
                autocomplete="new-password"
                class="form-control"
                style="background:#1f2937;border-color:#374151;color:#fff"
            />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <label class="d-block mb-1" style="color:#9ca3af;font-size:0.875rem">Confirm Password</label>
            <input
                type="password"
                name="password_confirmation"
                autocomplete="new-password"
                class="form-control"
                style="background:#1f2937;border-color:#374151;color:#fff"
            />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="d-flex align-items-center gap-3 pt-1">

            <button
                type="submit"
                class="btn"
                style="background:#4f46e5;color:#fff">
                Save Changes
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    style="color:#4ade80;font-size:0.875rem">
                    Saved successfully
                </p>
            @endif

        </div>

    </form>

</section>