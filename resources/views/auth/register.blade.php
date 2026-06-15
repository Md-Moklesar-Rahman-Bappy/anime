<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-[#0b0e16] px-4">

        <div class="w-full max-w-md bg-[#111827] rounded-2xl shadow-2xl p-8 border border-gray-800">

            <!-- Title -->
            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-white">
                    Ani<span class="text-indigo-500">Stream</span>
                </h1>
                <p class="text-gray-400 text-sm mt-2">
                    Create your account
                </p>
            </div>

            <form method="POST" action="{{ route('auth.register') }}">
                @csrf

                <!-- Name -->
                <div>
                    <x-input-label for="name" value="Name" class="text-gray-300" />

                    <x-text-input
                        id="name"
                        name="name"
                        type="text"
                        :value="old('name')"
                        required
                        autofocus
                        class="mt-1 w-full bg-[#1f2937] border-gray-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    />

                    <x-input-error :messages="$errors->get('name')" class="mt-2 text-red-500" />
                </div>

                <!-- Email -->
                <div class="mt-4">
                    <x-input-label for="email" value="Email" class="text-gray-300" />

                    <x-text-input
                        id="email"
                        name="email"
                        type="email"
                        :value="old('email')"
                        required
                        class="mt-1 w-full bg-[#1f2937] border-gray-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    />

                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-500" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="password" value="Password" class="text-gray-300" />

                    <x-text-input
                        id="password"
                        name="password"
                        type="password"
                        required
                        class="mt-1 w-full bg-[#1f2937] border-gray-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    />

                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-500" />
                </div>

                <!-- Confirm Password -->
                <div class="mt-4">
                    <x-input-label for="password_confirmation" value="Confirm Password" class="text-gray-300" />

                    <x-text-input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        required
                        class="mt-1 w-full bg-[#1f2937] border-gray-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    />

                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-500" />
                </div>

                <!-- Button -->
                <button
                    type="submit"
                    class="w-full mt-6 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2 rounded-lg transition">
                    Create Account
                </button>

                <!-- Bottom -->
                <div class="text-center mt-6 text-gray-400 text-sm">
                    Already have an account?
                    <a href="{{ route('auth.login') }}"
                       class="text-indigo-400 hover:text-indigo-300 ml-1">
                        Login
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>