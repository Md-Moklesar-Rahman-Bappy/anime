@props([
    'name',
    'label' => 'Upload files',
    'accept' => 'image/*',
    'multiple' => false,
])

<div
    x-data="smartDropzone({
        name: @js($name),
        multiple: @js((bool) $multiple)
    })"
    x-init="init()"
    class="space-y-3"
>
    <div
        @dragover.prevent="drag = true"
        @dragleave.prevent="drag = false"
        @drop.prevent="handleDrop($event)"
        @click="$refs.input.click()"
        class="dropzone"
        :class="drag ? 'dropzone-active' : ''"
    >
        <input
            x-ref="input"
            type="file"
            name="{{ $name }}"
            accept="{{ $accept }}"
            @if($multiple) multiple @endif
            class="hidden"
            @change="handleInput($event)"
        >

        <div class="text-gray-300 text-sm font-medium">
            {{ $label }}
        </div>

        <div class="text-gray-500 text-xs mt-1">
            Drag & drop, or click to browse
        </div>
    </div>

    <template x-if="files.length">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <template x-for="(item, index) in files" :key="item.id">
                <div
                    draggable="true"
                    @dragstart="startDrag(index)"
                    @dragover.prevent
                    @drop.prevent="dropOn(index)"
                    class="bg-gray-900 border border-gray-700 rounded-lg overflow-hidden"
                >
                    <template x-if="item.preview">
                        <img :src="item.preview" class="w-full aspect-[3/4] object-cover">
                    </template>

                    <template x-if="!item.preview">
                        <div class="aspect-[3/4] flex items-center justify-center text-gray-500 text-xs">
                            File
                        </div>
                    </template>

                    <div class="p-2">
                        <div class="text-xs text-gray-300 truncate" x-text="item.name"></div>
                        <div class="text-[11px] text-gray-500" x-text="item.size"></div>

                        <div class="h-1 bg-gray-800 rounded mt-2 overflow-hidden">
                            <div
                                class="h-full bg-indigo-500 transition-all"
                                :style="`width:${item.progress || 0}%`"
                            ></div>
                        </div>

                        <button
                            type="button"
                            @click.stop="removeFile(index)"
                            class="text-xs text-red-400 hover:text-red-300 mt-2"
                        >
                            Remove
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </template>

    <template x-if="multiple && files.length > 1">
        <p class="text-xs text-gray-500">
            Drag previews to reorder before saving.
        </p>
    </template>
</div>
