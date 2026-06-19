<section class="d-flex flex-column gap-3">

    <header>
        <h2 class="fw-semibold" style="color:#fff;font-size:1.125rem">
            Profile Information
        </h2>

        <p class="mt-1" style="color:#9ca3af;font-size:0.875rem">
            Update your name and email address.
        </p>
    </header>

    <form id="send-verification" method="POST" action="{{ route('auth.verification.send') }}">
        @csrf
    </form>

    <form method="POST" action="{{ route('profile.update') }}" class="d-flex flex-column gap-3">
        @csrf
        @method('PATCH')

        <div>
            <label class="d-block mb-1" style="color:#9ca3af;font-size:0.875rem">Name</label>
            <input
                type="text"
                name="name"
                value="{{ old('name', $user->name) }}"
                required
                autofocus
                autocomplete="name"
                class="form-control"
                style="background:#1f2937;border-color:#374151;color:#fff"
            />

            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <label class="d-block mb-1" style="color:#9ca3af;font-size:0.875rem">Email</label>
            <input
                type="email"
                name="email"
                value="{{ old('email', $user->email) }}"
                required
                autocomplete="username"
                class="form-control"
                style="background:#1f2937;border-color:#374151;color:#fff"
            />

            <x-input-error :messages="$errors->get('email')" class="mt-2" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())

                <div class="mt-2" style="color:#9ca3af;font-size:0.875rem">
                    Your email is not verified.

                    <button
                        form="send-verification"
                        class="ms-1 text-decoration-underline"
                        style="color:#818cf8;background:none;border:none;cursor:pointer">
                        Resend verification email
                    </button>
                </div>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2" style="color:#4ade80;font-size:0.875rem">
                        Verification link sent.
                    </p>
                @endif

            @endif
        </div>

        <div class="d-flex align-items-center gap-3 pt-1">

            <button
                type="submit"
                class="btn"
                style="background:#4f46e5;color:#fff">
                Save Changes
            </button>

            @if (session('status') === 'profile-updated')
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