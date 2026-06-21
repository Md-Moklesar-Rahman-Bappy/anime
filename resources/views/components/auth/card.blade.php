@props([
    'subtitle' => null,
    'info'     => null,
    'icon'     => null,
    'status'   => null,
])

<x-guest-layout>

    <div class="min-h-screen flex items-center justify-center bg-[#0b0e16] px-4 py-10">

        <div {{ $attributes->merge([
            'class' => 'w-full max-w-md rounded-2xl border border-gray-800 bg-[#111827] shadow-xl'
        ]) }}>

            <div class="p-6 sm:p-8">

                {{-- BRAND --}}
                <div class="text-center mb-6">
                    <a {{ url('/') }} class="inline-block">
                        <h3 class="text-2xl font-bold text-white">
                            Ani<span class="text-indigo-500">Stream</span>
                        </h3>
                    </a>

                    @if ($subtitle)
                        <p class="mt-2 text-sm text-gray-400">
                            {{ $subtitle }}
                        </p>
                    @endif
                </div>

                {{-- OPTIONAL ICON --}}
                @if ($icon)
                    <div class="flex justify-center mb-5">
                        <div class="w-16 h-16 rounded-full bg-indigo-500/15 border border-indigo-500/30
                                    flex items-center justify-center text-indigo-400">
                            {!! $icon !!}
                        </div>
                    </div>
                @endif

                {{-- OPTIONAL INFO BANNER --}}
                @if ($info)
                    <div class="mb-5 rounded-lg border border-gray-700/60 bg-[#0a0a0f]
                                px-4 py-3 text-center text-sm text-gray-400">
                        {{ $info }}
                    </div>
                @endif

                {{-- OPTIONAL STATUS (success message) --}}
                @if ($status)
                    <div
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 6000)"
                        class="mb-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10
                               px-4 py-3 text-center text-sm text-emerald-400"
                    >
                        ✓ {{ $status }}
                    </div>
                @endif

                {{-- MAIN CONTENT --}}
                {{ $slot }}

            </div>

            {{-- OPTIONAL FOOTER SLOT --}}
            @isset($footer)
                <div class="border-t border-gray-800 px-6 sm:px-8 py-4 text-center text-xs text-gray-500">
                    {{ $footer }}
                </div>
            @endisset

        </div>

        {{-- COPYRIGHT --}}
        <p class="absolute bottom-4 text-center text-xs text-gray-600 w-full">
            © {{ date('Y') }} AniStream. All rights reserved.
        </p>

    </div>

</x-guest-layout>