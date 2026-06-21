<section class="space-y-5">

    {{-- ─────── HEADER ─────── --}}
    <header>
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-indigo-500/15 border border-indigo-500/30 flex items-center justify-center">
                <i class="fas fa-key text-indigo-400 text-sm"></i>
            </div>
            <h2 class="text-lg font-semibold text-white">
                Update Password
            </h2>
        </div>

        <p class="mt-2 text-sm text-gray-400">
            Use a strong, unique password to keep your account secure.
        </p>
    </header>

    {{-- ─────── FORM ─────── --}}
    {{ route('auth.password.update') }}
          method="POST"
          class="space-y-4"
          x-data="passwordForm()">
        @csrf
        @method('PUT')

        {{-- CURRENT PASSWORD --}}
        <div>
            <label for="current_password" class="form-label">
                Current Password
            </label>

            <div class="relative" x-data="{ showCurrent: false }">
                <input
                    id="current_password"
                    name="current_password"
                    ::type="showCurrent ? 'text' : 'password'"
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="form-input pr-10"
                >

                <button type="button"
                        @click="showCurrent = !showCurrent"
                        class="absolute inset-y-0 right-2 flex items-center text-gray-400 hover:text-white transition"
                        :aria-label="showCurrent ? 'Hide password' : 'Show password'">
                    <svg x-show="!showCurrent" xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M1.5 12s4-7.5 10.5-7.5S22.5 12 22.5 12s-4 7.5-10.5 7.5S1.5 12 1.5 12z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <svg x-show="showCurrent" xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 3l18 18M10.6 10.6a3 3 0 104.2 4.2M9.9 4.6A10.7 10.7 0 0112 4.5c6.5 0 10.5 7.5 10.5 7.5a17 17 0 01-3.3 4.3M6.2 6.2A17.4 17.4 0 001.5 12s4 7.5 10.5 7.5c1.4 0 2.7-.3 3.9-.7" />
                    </svg>
                </button>
            </div>

            <x-input-error
                :messages="$errors->updatePassword->get('current_password')"
                class="mt-1 text-xs text-red-400"
            />
        </div>

        {{-- NEW PASSWORD --}}
        <div>
            <label for="password" class="form-label">
                New Password
            </label>

            <div class="relative">
                <input
                    id="password"
                    name="password"
                    ::type="showPassword ? 'text' : 'password'"
                    autocomplete="new-password"
                    placeholder="••••••••"
                    x-model="password"
                    class="form-input pr-10"
                >

                <button type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-2 flex items-center text-gray-400 hover:text-white transition"
                        :aria-label="showPassword ? 'Hide password' : 'Show password'">
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

            {{-- STRENGTH METER --}}
            <div class="mt-2" x-show="password.length > 0" x-transition>
                <div class="h-1.5 w-full rounded-full bg-gray-800 overflow-hidden">
                    <div
                        class="h-full transition-all duration-300"
                        :class="strengthColor"
                        :style="`width: ${strengthPercent}%`"
                    ></div>
                </div>
                <p class="mt-1 text-xs" :class="strengthTextColor" x-text="strengthLabel"></p>
            </div>

            <x-input-error
                :messages="$errors->updatePassword->get('password')"
                class="mt-1 text-xs text-red-400"
            />
        </div>

        {{-- CONFIRM PASSWORD --}}
        <div>
            <label for="password_confirmation" class="form-label">
                Confirm Password
            </label>

            <div class="relative">
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    ::type="showPassword ? 'text' : 'password'"
                    autocomplete="new-password"
                    placeholder="••••••••"
                    x-model="passwordConfirm"
                    class="form-input pr-10"
                >

                {{-- MATCH INDICATOR --}}
                <div
                    class="absolute inset-y-0 right-2 flex items-center"
                    x-show="passwordConfirm.length > 0"
                    x-transition
                >
                    <span x-show="password === passwordConfirm"
                          class="text-emerald-400 text-sm font-bold">✓</span>
                    <span x-show="password !== passwordConfirm"
                          class="text-red-400 text-sm font-bold">✗</span>
                </div>
            </div>

            <x-input-error
                :messages="$errors->updatePassword->get('password_confirmation')"
                class="mt-1 text-xs text-red-400"
            />
        </div>

        {{-- ACTIONS --}}
        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i>
                Save Changes
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="text-sm text-emerald-400 flex items-center gap-1"
                >
                    <i class="fas fa-check-circle"></i>
                    Password updated successfully
                </p>
            @endif
        </div>

    </form>

</section>