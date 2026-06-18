<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-[#0b0e16] px-4">

        <div class="w-full max-w-md bg-[#111827] rounded-2xl shadow-2xl p-8 border border-gray-800">

            <!-- Title -->
            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-white">
                    Ani<span class="text-indigo-500">Stream</span>
                </h1>
                <p class="text-gray-400 text-sm mt-2">
                    Reset your password
                </p>
            </div>

            <!-- Info -->
            <div class="mb-4 text-sm text-gray-400 text-center">
                Forgot your password? Enter your email and we’ll send you a reset link.
            </div>

            <!-- Status -->
            <x-auth-session-status
                class="mb-4 text-center"
                :status="session('status')"
            />

            <!-- Form -->
            <form method="POST" action="{{ route('auth.password.email') }}">
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

                    <x-input-error
                        :messages="$errors->get('email')"
                        class="mt-2 text-red-400"
                    />
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    class="w-full mt-6 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 rounded-lg transition">
                    Send Reset Link
                </button>

                <!-- Back -->
                <div class="text-center mt-6 text-gray-400 text-sm">
                    Remember your password?
                    <a href="{{ route('auth.login') }}"
                       class="text-indigo-400 hover:text-indigo-300 ml-1 transition">
                        Login
                    </a>
                </div>

            </form>
        </div>
    </div>
</x-guest-layout>