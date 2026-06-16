<section class="space-y-6">

    <!-- Header -->
    <header>
        <h2 class="text-lg font-semibold text-white">
            Delete Account
        </h2>

        <p class="mt-1 text-sm text-gray-400">
            Once your account is deleted, all of its data will be permanently removed.
            Please download anything you want to keep before proceeding.
        </p>
    </header>

    <!-- Trigger -->
    <button
        x-data
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded-lg text-sm transition">
        Delete Account
    </button>

    <!-- Modal -->
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>

        <form method="POST"
              action="{{ route('profile.destroy') }}"
              class="p-6 space-y-4">

            @csrf
            @method('DELETE')

            <!-- Title -->
            <h2 class="text-lg font-semibold text-white">
                Confirm Account Deletion
            </h2>

            <p class="text-sm text-gray-400">
                This action is irreversible. Enter your password to confirm.
            </p>

            <!-- Password -->
            <div>
                <input
                    id="password"
                    name="password"
                    type="password"
                    placeholder="Password"
                    class="w-full mt-1 px-3 py-2 bg-[#1f2937] border border-gray-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                />

                <x-input-error
                    :messages="$errors->userDeletion->get('password')"
                    class="mt-2 text-red-400"
                />
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-2">

                <button
                    type="button"
                    x-on:click="$dispatch('close')"
                    class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm transition">
                    Cancel
                </button>

                <button
                    type="submit"
                    class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded-lg text-sm transition">
                    Delete
                </button>

            </div>

        </form>

    </x-modal>
</section>
