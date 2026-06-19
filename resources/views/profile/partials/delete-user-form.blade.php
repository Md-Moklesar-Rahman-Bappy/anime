<section class="d-flex flex-column gap-3">

    <header>
        <h2 class="fw-semibold" style="color:#fff;font-size:1.125rem">
            Delete Account
        </h2>

        <p class="mt-1" style="color:#9ca3af;font-size:0.875rem">
            Once your account is deleted, all of its data will be permanently removed.
            Please download anything you want to keep before proceeding.
        </p>
    </header>

    <button
        x-data
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="btn"
        style="background:#dc2626;color:#fff">
        Delete Account
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>

        <form method="POST"
              action="{{ route('profile.destroy') }}"
              class="d-flex flex-column gap-3">

            @csrf
            @method('DELETE')

            <h2 class="fw-semibold" style="color:#fff;font-size:1.125rem">
                Confirm Account Deletion
            </h2>

            <p style="color:#9ca3af;font-size:0.875rem">
                This action is irreversible. Enter your password to confirm.
            </p>

            <div>
                <input
                    id="password"
                    name="password"
                    type="password"
                    placeholder="Password"
                    class="form-control"
                    style="background:#1f2937;border-color:#374151;color:#fff"
                />

                <x-input-error
                    :messages="$errors->userDeletion->get('password')"
                    class="mt-2"
                />
            </div>

            <div class="d-flex justify-content-end gap-2 pt-1">

                <button
                    type="button"
                    x-on:click="$dispatch('close')"
                    class="btn"
                    style="background:#1f2937;color:#fff">
                    Cancel
                </button>

                <button
                    type="submit"
                    class="btn"
                    style="background:#dc2626;color:#fff">
                    Delete
                </button>

            </div>

        </form>

    </x-modal>
</section>
