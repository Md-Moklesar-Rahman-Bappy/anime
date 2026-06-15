<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-[#0b0e16] px-4">

        <div class="w-full max-w-md bg-[#111827] rounded-2xl shadow-2xl p-8 border border-gray-800">

            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-white">
                    Ani<span class="text-indigo-500">Stream</span>
                </h1>
                <p class="text-gray-400 text-sm mt-2">
                    Verify your email
                </p>
            </div>

            <div class="mb-4 text-sm text-gray-400 text-center">
                Thanks for signing up! Please verify your email address by clicking the link we just sent to you.
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="mb-4 text-green-500 text-sm text-center">
                    A new verification link has been sent to your email address.
                </div>
            @endif

            <div class="flex flex-col gap-4 mt-4">

                <!-- Resend -->
                <form method="POST" action="{{ route('auth.verification.send') }}">
                    @csrf
                    <button
                        type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2 rounded-lg transition">
                        Resend Verification Email
                    </button>
                </form>

                <!-- Logout -->
                <form method="POST" action="{{ route('auth.logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="w-full text-sm text-gray-400 hover:text-white transition">
                        Log Out
                    </button>
                </form>

            </div>
        </div>
    </div>
</x-guest-layout>