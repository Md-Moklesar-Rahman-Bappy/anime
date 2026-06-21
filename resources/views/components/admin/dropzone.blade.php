@props([
    'name',
    'label'    => 'Upload files',
    'hint'     => null,
    'accept'   => 'image/*',
    'multiple' => false,
    'maxSize'  => 5,            // in MB
    'existing' => null,         // existing image URL (for edit forms)
    'disabled' => false,
    'aspect'   => 'aspect-[3/4]', // tailwind aspect ratio class
])

<div
    x-data="smartDropzone({
        name: @js($name),
        multiple: @js((bool) $multiple),
        accept: @js($accept),
        maxSize: @js((int) $maxSize),
        existing: @js($existing),
    })"
    x-init="init()"
    class="space-y-3"
>

    {{-- DROPZONE --}}
    <div
        @dragover.prevent="drag = true"
        @dragleave.prevent="drag = false"
        @drop.prevent="handleDrop($event)"
        @click="!{{ $disabled ? 'true' : 'false' }} && $refs.input.click()"
        @keydown.enter.prevent="$refs.input.click()"
        @keydown.space.prevent="$refs.input.click()"
        role="button"
        tabindex="0"
        :aria-disabled="{{ $disabled ? 'true' : 'false' }}"
        class="relative rounded-xl border-2 border-dashed transition-all cursor-pointer
               flex flex-col items-center justify-center text-center px-4 py-8
               focus:outline-none focus:ring-2 focus:ring-indigo-500
               {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}"
        :class="drag
            ? 'border-indigo-500 bg-indigo-500/10'
            : 'border-gray-700 bg-[#0a0a0f] hover:border-gray-600 hover:bg-[#0f0f17]'"
    >
        <input
            x-ref="input"
            type="file"
            name="{{ $name }}{{ $multiple ? '[]' : '' }}"
            accept="{{ $accept }}"
            @if($multiple) multiple @endif
            @if($disabled) disabled @endif
            class="hidden"
            @change="handleInput($event)"
        >

        {{-- ICON --}}
        <svg xmlns="http://www.w3.org/2000/svg"
             class="w-10 h-10 mb-2 transition-colors"
             :class="drag ? 'text-indigo-400' : 'text-gray-500'"
             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 7.5m0 0L7.5 12m4.5-4.5v12.75" />
        </svg>

        <div class="text-gray-200 text-sm font-medium">
            {{ $label }}
        </div>

        <div class="text-gray-500 text-xs mt-1">
            Drag &amp; drop, or click to browse
        </div>

        {{-- HINT (file types / size) --}}
        <div class="text-gray-600 text-[11px] mt-2">
            {{ $hint ?? 'Max ' . $maxSize . 'MB' . ($accept !== '*' ? ' • ' . str_replace('/*', '', $accept) : '') }}
        </div>
    </div>

    {{-- ERROR MESSAGES --}}
    <template x-if="errors.length">
        <div class="rounded-lg border border-red-500/30 bg-red-500/10 px-3 py-2">
            <template x-for="(err, i) in errors" :key="i">
                <p class="text-xs text-red-400" x-text="err"></p>
            </template>
        </div>
    </template>

    {{-- FILE PREVIEWS --}}
    <template x-if="files.length">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <template x-for="(item, index) in files" :key="item.id">
                <div
                    draggable="true"
                    @dragstart="startDrag(index)"
                    @dragover.prevent
                    @drop.prevent="dropOn(index)"
                    class="relative group bg-gray-900 border border-gray-700 rounded-lg overflow-hidden transition hover:border-gray-500"
                >
                    {{-- IMAGE / FILE PREVIEW --}}
                    <template x-if="item.preview">
                        <img
                            :src="item.preview"
                            class="w-full {{ $aspect }} object-cover"
                            :alt="item.name"
                            loading="lazy"
                        >
                    </template>

                    <template x-if="!item.preview">
                        <div class="{{ $aspect }} flex flex-col items-center justify-center text-gray-500 text-xs bg-gray-800">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-8 h-8 mb-1" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25M9 16.5v.75m3-3v3M15 12v5.25m-4.5-9H8.25m6 0a3 3 0 11-6 0 3 3 0 016 0zM3.75 19.5h16.5a1.5 1.5 0 001.5-1.5V8.25a1.5 1.5 0 00-1.5-1.5h-3.75" />
                            </svg>
                            File
                        </div>
                    </template>

                    {{-- REMOVE BUTTON (X icon, top right) --}}
                    <button
                        type="button"
                        @click.stop="removeFile(index)"
                        class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-black/60 hover:bg-red-500
                               flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition"
                        aria-label="Remove file"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="w-3.5 h-3.5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    {{-- INFO --}}
                    <div class="p-2">
                        <div class="text-xs text-gray-300 truncate" x-text="item.name"></div>
                        <div class="text-[11px] text-gray-500" x-text="item.size"></div>

                        {{-- PROGRESS BAR --}}
                        <div
                            class="h-1 bg-gray-800 rounded mt-2 overflow-hidden"
                            x-show="item.progress > 0 && item.progress < 100"
                        >
                            <div
                                class="h-full bg-indigo-500 transition-all"
                                :style="`width:${item.progress || 0}%`"
                            ></div>
                        </div>

                        {{-- UPLOADED STATUS --}}
                        <div
                            x-show="item.progress === 100"
                            class="text-[11px] text-emerald-400 mt-1"
                        >
                            ✓ Uploaded
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </template>

    {{-- REORDER HINT --}}
    <template x-if="multiple && files.length > 1">
        <p class="text-xs text-gray-500 flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
            </svg>
            Drag previews to reorder before saving.
        </p>
    </template>

</div>