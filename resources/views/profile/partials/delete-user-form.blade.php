<section class="space-y-5">

    {{-- ─────── HEADER ─────── --}}
    <header>
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-red-500/15 border border-red-500/30 flex items-center justify-center">
                <i class="fas fa-triangle-exclamation text-red-400 text-sm"></i>
            </div>
            <h2 class="text-lg font-semibold text-white">
                Delete Account
            </h2>
        </div>

        <p class="mt-2 text-sm text-gray-400">
            Once your account is deleted, <strong class="text-red-400">all of its data will be permanently removed</strong>.
            Please download anything you'd like to keep before proceeding.
        </p>
    </header>

    {{-- ─────── DANGER ZONE ─────── --}}
    <div class="rounded-xl border border-red-500/30 bg-red-500/5 p-4">
        <p class="text-sm text-red-300 mb-3">
            ⚠ This action is <strong>irreversible</strong> — your profile, comments, watchlist, history, and uploads will be permanently removed.
        </p>

        <button
            x-data
            @click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="btn-danger btn-sm"
        >
            <i class="fas fa-trash-can"></i>
            Delete Account
        </button>
    </div>

    {{-- ─────── CONFIRMATION MODAL ─────── --}}
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>

        {{ route('profile.destroy') }}"
              method="POST"
              class="p-6 space-y-5"
              x-data="{
                  showPassword: false,
                  confirmText: '',
                  isValid() { return this.confirmText === 'DELETE'; }
              }"
        >
            @csrf
            @method('DELETE')

            {{-- TITLE --}}
            <div class="text-center">
                <div class="mx-auto w-14 h-14 rounded-full bg-red-500/15 border border-red-500/30 flex items-center justify-center mb-3">
                    <i class="fas fa-triangle-exclamation text-red-400 text-2xl"></i>
                </div>

                <h2 class="text-lg font-semibold text-white">
                    Are you absolutely sure?
                </h2>

                <p class="mt-2 text-sm text-gray-400">
                    This action <strong class="text-red-400">cannot be undone</strong>.
                    Your account and all related data will be permanently deleted.
                </p>
            </div>

            {{-- TYPE TO CONFIRM --}}
            <div>
                <label for="confirm-delete" class="form-label">
                    Type <span class="text-red-400 font-bold">DELETE</span> to confirm:
                </label>

                <input
                    id="confirm-delete"
                    type="text"
                    x-model="confirmText"
                    autocomplete="off"
                    placeholder="DELETE"
                    class="form-input"
                    :class="confirmText && !isValid() ? 'field-error' : ''"
                >
            </div>

            {{-- PASSWORD --}}
            <div>
                <label for="password" class="form-label">
                    Confirm with password:
                </label>

                <div class="relative">
                    <input
                        id="password"
                        name="password"
                        ::type="showPassword ? 'text' : 'password'"
                        placeholder="••••••••"
                        autocomplete="current-password"
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

                <x-input-error
                    :messages="$errors->userDeletion->get('password')"
                    class="mt-1 text-xs text-red-400"
                />
            </div>

            {{-- ACTIONS --}}
            <div class="flex justify-end gap-2 pt-2">

                <button type="button"
                        @click="$dispatch('close')"
                        class="btn-cancel">
                    Cancel
                </button>

                <button type="submit"
                        class="btn-danger"
                        :disabled="!isValid()"
                        :class="!isValid() && 'opacity-50 cursor-not-allowed'">
                    <i class="fas fa-trash-can"></i>
                    Permanently Delete
                </button>

            </div>

        </form>

    </x-modal>
</section>