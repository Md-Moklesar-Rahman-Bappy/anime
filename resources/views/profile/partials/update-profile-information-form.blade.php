<section class="space-y-6">

    <!-- Header -->
    <header>
        <h2 class="text-lg font-semibold text-white">
            Profile Information
        </h2>

        <p class="mt-1 text-sm text-gray-400">
            Update your name and email address.
        </p>
    </header>

    <!-- Resend Verification -->
    <form id="send-verification" method="POST" action="{{ route('auth.verification.send') }}">
        @csrf
    </form>

    <!-- Main Form -->
    <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
        @csrf
        @method('PATCH')

        <!-- Name -->
        <div>
            <label class="text-sm text-gray-400 block mb-1">Name</label>
            <input
                type="text"
                name="name"
                value="{{ old('name', $user->name) }}"
                required
                autofocus
                autocomplete="name"
                class="form-input"
            />

            <x-input-error :messages="$errors->get('name')" class="mt-2 text-red-400" />
        </div>

        <!-- Email -->
        <div>
            <label class="text-sm text-gray-400 block mb-1">Email</label>
            <input
                type="email"
                name="email"
                value="{{ old('email', $user->email) }}"
                required
                autocomplete="username"
                class="form-input"
            />

            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())

                <div class="mt-3 text-sm text-gray-400">
                    Your email is not verified.

                    <button
                        form="send-verification"
                        class="ml-1 text-indigo-400 hover:text-indigo-300 underline">
                        Resend verification email
                    </button>
                </div>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 text-sm text-green-400">
                        Verification link sent.
                    </p>
                @endif

            @endif
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-4 pt-2">

            <button
                type="submit"
                class="bg-indigo-600 hover:bg-indigo-500 px-5 py-2 rounded-lg text-white text-sm font-medium transition">
                Save Changes
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-400">
                    Saved successfully
                </p>
            @endif

        </div>

    </form>

</section>

<style>
.form-input {
    @apply w-full px-3 py-2 bg-[#1f2937] border border-gray-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500;
}
</style>