<section class="space-y-6">

    <!-- Header -->
    <header>
        <h2 class="text-lg font-semibold text-white">
            Update Password
        </h2>

        <p class="mt-1 text-sm text-gray-400">
            Use a strong, unique password to keep your account secure.
        </p>
    </header>

    <!-- Form -->
    <form method="POST" action="{{ route('auth.password.update') }}" class="space-y-4">
        @csrf
        @method('PUT')

        <!-- Current Password -->
        <div>
            <label class="text-sm text-gray-400 block mb-1">Current Password</label>
            <input
                type="password"
                name="current_password"
                autocomplete="current-password"
                class="form-input"
            />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2 text-red-400" />
        </div>

        <!-- New Password -->
        <div>
            <label class="text-sm text-gray-400 block mb-1">New Password</label>
            <input
                type="password"
                name="password"
                autocomplete="new-password"
                class="form-input"
            />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2 text-red-400" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label class="text-sm text-gray-400 block mb-1">Confirm Password</label>
            <input
                type="password"
                name="password_confirmation"
                autocomplete="new-password"
                class="form-input"
            />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2 text-red-400" />
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-4 pt-2">

            <button
                type="submit"
                class="bg-indigo-600 hover:bg-indigo-500 px-5 py-2 rounded-lg text-white text-sm font-medium transition">
                Save Changes
            </button>

            @if (session('status') === 'password-updated')
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