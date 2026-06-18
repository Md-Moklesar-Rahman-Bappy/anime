<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-[#0b0e16] px-4">

        <div class="w-full max-w-md bg-[#111827] rounded-2xl shadow-2xl p-8 border border-gray-800">

            <!-- Logo / Title -->
            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-white">
                    Ani<span class="text-indigo-500">Stream</span>
                </h1>
                <p class="text-gray-400 text-sm mt-2">
                    Confirm your password
                </p>
            </div>

            <!-- Info -->
            <div class="mb-4 text-sm text-gray-400 text-center">
                This is a secure area. Please confirm your password to continue.
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('auth.password.confirm') }}">
                @csrf

                <!-- Password -->
                <div>
                    <x-input-label for="password" value="Password" class="text-gray-300" />

                    <x-text-input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autofocus
                        autocomplete="current-password"
                        class="mt-1 w-full bg-[#1f2937] border-gray-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    />

                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400" />
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    class="w-full mt-6 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 rounded-lg transition">
                    Confirm Password
                </button>

                <!-- Back -->
                <div class="text-center mt-6 text-gray-400 text-sm">
                    Back to
                    <a href="{{ route('auth.login') }}"
                       class="text-indigo-400 hover:text-indigo-300 ml-1 transition">
                        Login
                    </a>
                </div>
            </form>

        </div>
    </div>
</x-guest-layout>