<section class="space-y-5">

    {{-- ─────── HEADER ─────── --}}
    <header>
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-indigo-500/15 border border-indigo-500/30 flex items-center justify-center">
                <i class="fas fa-user text-indigo-400 text-sm"></i>
            </div>
            <h2 class="text-lg font-semibold text-white">
                Profile Information
            </h2>
        </div>

        <p class="mt-2 text-sm text-gray-400">
            Update your account's profile information and email address.
        </p>
    </header>

    {{-- HIDDEN VERIFICATION FORM --}}
    {{ route('auth.verification.send') }}>
        @csrf
    </form>

    {{-- ─────── MAIN FORM ─────── --}}
    {{ route('profile.update') }}
        @csrf
        @method('PATCH')

        {{-- AVATAR PREVIEW --}}
        <div class="flex items-center gap-4 pb-2">
            ={{ urlencode($user->name) }}&background=6366f1&color=fff&size=128"
                 class="w-16 h-16 rounded-full border-2 border-gray-800"
                 alt="{{ $user->name }}"
            >

            <div>
                <p class="text-sm font-medium text-white">{{ $user->name }}</p>
                <p class="text-xs text-gray-500">{{ $user->email }}</p>

                @if ($user->email_verified_at)
                    <span class="inline-flex items-center gap-1 mt-1 text-xs text-emerald-400">
                        <i class="fas fa-circle-check"></i> Verified
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 mt-1 text-xs text-amber-400">
                        <i class="fas fa-circle-exclamation"></i> Unverified
                    </span>
                @endif
            </div>
        </div>

        {{-- NAME --}}
        <div>
            <label for="name" class="form-label">
                Name
            </label>

            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name', $user->name) }}"
                required
                autofocus
                autocomplete="name"
                placeholder="Your name"
                class="form-input"
            >

            <x-input-error
                :messages="$errors->get('name')"
                class="mt-1 text-xs text-red-400"
            />
        </div>

        {{-- EMAIL --}}
        <div>
            <label for="email" class="form-label">
                Email Address
            </label>

            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $user->email) }}"
                required
                autocomplete="username"
                placeholder="you@example.com"
                class="form-input"
            >

            <x-input-error
                :messages="$errors->get('email')"
                class="mt-1 text-xs text-red-400"
            />

            {{-- UNVERIFIED EMAIL WARNING --}}
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())

                <div class="mt-3 rounded-lg border border-amber-500/30 bg-amber-500/10 p-3">
                    <p class="text-xs text-amber-300 flex items-start gap-2">
                        <i class="fas fa-circle-exclamation mt-0.5"></i>

                        <span>
                            Your email address is unverified.
                            <button
                                form="send-verification"
                                type="submit"
                                class="ml-1 text-amber-200 hover:text-white underline transition"
                            >
                                Click here to resend the verification email.
                            </button>
                        </span>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-xs text-emerald-400 flex items-center gap-1">
                            <i class="fas fa-check-circle"></i>
                            A new verification link has been sent to your email.
                        </p>
                    @endif
                </div>

            @endif
        </div>

        {{-- ACTIONS --}}
        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i>
                Save Changes
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="text-sm text-emerald-400 flex items-center gap-1"
                >
                    <i class="fas fa-check-circle"></i>
                    Profile updated successfully
                </p>
            @endif
        </div>

    </form>

</section>