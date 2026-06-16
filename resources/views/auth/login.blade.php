<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-[#0b0e16] px-4">

        <div class="w-full max-w-md bg-[#111827] rounded-2xl shadow-2xl p-8 border border-gray-800">

            <!-- Title -->
            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-white">
                    Ani<span class="text-indigo-500">Stream</span>
                </h1>
                <p class="text-gray-400 text-sm mt-2">
                    Welcome back, sign in to continue
                </p>
            </div>

            <!-- Status -->
            <x-auth-session-status class="mb-4 text-center" :status="session('status')" />

            <!-- Form -->
            <form method="POST" action="{{ route('auth.login') }}">
                @csrf

                <!-- Email -->
                <div>
                    <x-input-label for="email" value="Email" class="text-gray-300" />

                    <x-text-input
                        id="email"
                        name="email"
                        type="email"
                        :value="old('email')"
                        required
                        autofocus
                        class="mt-1 w-full bg-[#1f2937] border-gray-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-400"
                    />

                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="password" value="Password" class="text-gray-300" />

                    <x-text-input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="mt-1 w-full bg-[#1f2937] border-gray-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    />

                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400" />
                </div>

                <!-- Options -->
                <div class="flex items-center justify-between mt-4 text-sm">

                    <label class="flex items-center text-gray-400">
                        <input type="checkbox" name="remember" class="mr-2 accent-indigo-500">
                        Remember me
                    </label>

                    @if (Route::has('auth.password.request'))
                        <a href="{{ route('auth.password.request') }}"
                           class="text-indigo-400 hover:text-indigo-300 transition">
                            Forgot password?
                        </a>
                    @endif

                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    class="w-full mt-6 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 rounded-lg transition">
                    Sign In
                </button>

                <!-- Bottom -->
                <div class="text-center mt-6 text-gray-400 text-sm">
                    Don’t have an account?
                    <a href="{{ route('auth.register') }}"
                       class="text-indigo-400 hover:text-indigo-300 ml-1 transition">
                        Register
                    </a>
                </div>

            </form>
        </div>
    </div>
</x-guest-layout>