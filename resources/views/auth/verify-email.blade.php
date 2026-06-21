<x-auth.card
    subtitle="Verify your email"
    info="Thanks for signing up! Please verify your email by clicking the link we sent to your inbox."
    :status="session('status') == 'verification-link-sent' ? 'A new verification link has been sent to your email.' : null"
>

    {{-- ENVELOPE ICON --}}
    <x-slot:icon>
        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15A2.25 2.25 0 012.25 17.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
        </svg>
    </x-slot:icon>

    {{-- ACTIONS --}}
    <div class="space-y-3">

        {{-- Resend verification --}}
        {{ route('auth.verification.send') }}
            @csrf
            <button
                type="submit"
                class="w-full rounded-lg bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700
                       py-2.5 font-semibold text-white transition
                       focus:outline-none focus:ring-2 focus:ring-indigo-500
                       focus:ring-offset-2 focus:ring-offset-[#111827]"
            >
                Resend Verification Email
            </button>
        </form>

        {{-- Logout --}}
        {{ route('auth.logout') }}
            @csrf
            <button
                type="submit"
                class="w-full text-center text-sm text-gray-400 hover:text-white transition py-2"
            >
                Log Out
            </button>
        </form>

    </div>

    {{-- HELP FOOTER --}}
    <x-slot:footer>
        📬 Didn't receive the email? Check your spam folder or click "Resend" above.
    </x-slot:footer>

</x-auth.card>