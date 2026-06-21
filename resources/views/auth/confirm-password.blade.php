<x-auth.card
    subtitle="Confirm your password"
    info="🔒 This is a secure area. Please confirm your password to continue."
    :status="session('status')"
>

    {{ route('auth.password.confirm') }}
          x-data="{ showPassword: false }">
        @csrf

        {{-- PASSWORD --}}
        <div>
            <x-input-label
                for="password"
                value="Password"
                class="block text-sm font-medium text-gray-300 mb-1"
            />

            <div class="relative">
                <x-text-input
                    id="password"
                    name="password"
                    ::type="showPassword ? 'text' : 'password'"
                    required
                    autofocus
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="pr-10"
                />

                {{-- SHOW / HIDE TOGGLE --}}
                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-2 flex items-center text-gray-400 hover:text-white transition"
                    :aria-label="showPassword ? 'Hide password' : 'Show password'"
                >
                    <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M1.5 12s4-7.5 10.5-7.5S22.5 12 22.5 12s-4 7.5-10.5 7.5S1.5 12 1.5 12z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>

                    <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 3l18 18M10.6 10.6a3 3 0 104.2 4.2M9.9 4.6A10.7 10.7 0 0112 4.5c6.5 0 10.5 7.5 10.5 7.5a17 17 0 01-3.3 4.3M6.2 6.2A17.4 17.4 0 001.5 12s4 7.5 10.5 7.5c1.4 0 2.7-.3 3.9-.7" />
                    </svg>
                </button>
            </div>

            <x-input-error
                :messages="$errors->get('password')"
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
            Confirm Password
        </button>

        {{-- BACK LINK --}}
        <div class="text-center text-sm text-gray-400 pt-2">
            Back to
            {{ route('auth.login') }} hover:text-indigo-300 hover:underline transition">
                Login
            </a>
        </div>

    </form>

</x-auth.card>