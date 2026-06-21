<div
    x-data="toastCenter()"
    class="fixed bottom-5 right-5 z-[9999] flex flex-col gap-2 max-w-sm w-full"
>
    <template x-for="t in toasts" :key="t.id">
        <div
            x-show="t.show"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="rounded-lg border px-4 py-3 shadow-lg backdrop-blur-sm flex items-start gap-2"
            :class="colorFor(t.type)"
        >
            <span class="font-bold" x-text="iconFor(t.type)"></span>

            <div class="flex-1 text-sm">
                <p x-text="t.message"></p>

                <div class="mt-2 flex gap-3" x-show="t.actionLabel || t.dismissLabel">
                    <button
                        type="button"
                        x-show="t.actionLabel"
                        @click="runAction(t.id)"
                        class="text-xs font-semibold underline hover:no-underline"
                        x-text="t.actionLabel"
                    ></button>

                    <button
                        type="button"
                        x-show="t.dismissLabel"
                        @click="dismiss(t.id)"
                        class="text-xs opacity-70 hover:opacity-100"
                        x-text="t.dismissLabel"
                    ></button>
                </div>
            </div>

            <button
                type="button"
                @click="dismiss(t.id)"
                class="opacity-60 hover:opacity-100 text-sm"
                aria-label="Close"
            >
                ✕
            </button>
        </div>
    </template>
</div>