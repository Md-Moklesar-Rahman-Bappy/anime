<x-guest-layout>
    <div class="min-vh-100 d-flex align-items-center justify-content-center" style="background: #0b0e16;">

        <div class="card" style="background: #111827; border-color: #1f2937; max-width: 28rem; width: 100%;">
            <div class="card-body p-4">

                <div class="text-center mb-4">
                    <h3 class="fw-bold" style="color: #fff;">
                        Ani<span style="color: #6366f1;">Stream</span>
                    </h3>
                    <p class="small mt-2" style="color: #9ca3af;">
                        Verify your email
                    </p>
                </div>

                <div class="mb-3 text-center" style="color: #9ca3af; font-size: 0.875rem;">
                    Thanks for signing up! Please verify your email by clicking the link we sent.
                </div>

                @if (session('status') == 'verification-link-sent')
                    <div class="mb-3 text-center" style="color: #22c55e; font-size: 0.875rem;">
                        A new verification link has been sent to your email.
                    </div>
                @endif

                <div class="d-flex flex-column gap-3 mt-3">

                    <form method="POST" action="{{ route('auth.verification.send') }}">
                        @csrf
                        <button
                            type="submit"
                            class="btn w-100 fw-semibold"
                            style="background: #4f46e5; border-color: #4f46e5; color: #fff;">
                            Resend Verification Email
                        </button>
                    </form>

                    <form method="POST" action="{{ route('auth.logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-link w-100 text-decoration-none"
                            style="color: #9ca3af; font-size: 0.875rem;">
                            Log Out
                        </button>
                    </form>

                </div>

            </div>
        </div>
    </div>
</x-guest-layout>
