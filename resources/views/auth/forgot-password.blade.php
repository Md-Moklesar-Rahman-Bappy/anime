<x-auth.card
    subtitle="Reset your password"
    info="🔑 Forgot your password? Enter your email and we'll send you a reset link."
    :status="session('status')"
>

    {{ route('auth.password.email') }}
        @csrf

        {{-- EMAIL --}}
        <div>
            <x-input-label
                for="email"
                value="Email"
                class="block text-sm font-medium text-gray-300 mb-1"
            />

            <x-text-input
                id="email"
                name="email"
                type="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="email"
                placeholder="you@example.com"
            />

            <x-input-error
                :messages="$errors->get('email')"
                class="mt-1 text-xs text-red-400"
            />
        </div>

        {{-- SUBMIT --}}
        <button
            type="submit"
            class="w-full rounded-lg bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700
                   py-2.5 font-semibold text-white transition
                   focus:outline-none focus:ring-2 focus:ring-indigo-500
                   focus:ring-offset-2 focus:ring-offset-[#111827]"
        >
            Send Reset Link
        </button>

        {{-- BACK LINK --}}
        <div class="text-center text-sm text-gray-400 pt-2">
            Remember your password?
            {{ route('auth.login') }} hover:text-indigo-300 hover:underline transition">
                Login
            </a>
        </div>

    </form>

</x-auth.card>