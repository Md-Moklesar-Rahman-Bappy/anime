<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-[#0b0e16] px-4">

        <div class="w-full max-w-md bg-[#111827] rounded-2xl shadow-2xl p-8 border border-gray-800">

            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-white">
                    Ani<span class="text-indigo-500">Stream</span>
                </h1>
                <p class="text-gray-400 text-sm mt-2">
                    Reset your password
                </p>
            </div>

            {{ route('auth.password.store') }}
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div>
                    <x-input-label for="email" value="Email" class="text-gray-300" />

                    <x-text-input
                        id="email"
                        name="email"
                        type="email"
                        :value="old('email', $request->email)"
                        required
                        autofocus
                        class="mt-1 w-full bg-[#1f2937] border-gray-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    />

                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-500" />
                </div>

                <div class="mt-4">
                    <x-input-label for="password" value="Password" class="text-gray-300" />

                    <x-text-input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="new-password"
                        class="mt-1 w-full bg-[#1f2937] border-gray-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    />

                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-500" />
                </div>

                <div class="mt-4">
                    <x-input-label for="password_confirmation" value="Confirm Password" class="text-gray-300" />

                    <x-text-input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        required
                        autocomplete="new-password"
                        class="mt-1 w-full bg-[#1f2937] border-gray-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    />

                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-500" />
                </div>

                <button
                    type="submit"
                    class="w-full mt-6 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2 rounded-lg transition">
                    Reset Password
                </button>

                <div class="text-center mt-6 text-gray-400 text-sm">
                    Back to
                     }}"
                       class="text-indigo-400 hover:text-indigo-300 ml-1">
                        Login
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>