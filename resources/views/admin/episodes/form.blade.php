@extends('admin.layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">{{ isset($episode) ? 'Edit' : 'Create' }} Episode for {{ $anime->title }}</h1>

    <form action="{{ isset($episode) ? route('admin.anime.episodes.update', [$anime, $episode]) : route('admin.anime.episodes.store', $anime) }}" method="POST" enctype="multipart/form-data" class="space-y-4" x-data="episodeForm()" @submit.prevent="submitForm">
        @csrf
        @if(isset($episode)) @method('PUT') @endif

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Episode Number</label>
                <input type="number" name="number" value="{{ old('number', $episode->number ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700" required>
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Duration (minutes)</label>
                <input type="number" name="duration" value="{{ old('duration', $episode->duration ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700">
            </div>
        </div>

        <div>
            <label class="block text-sm text-gray-400 mb-1">Title</label>
            <input type="text" name="title" value="{{ old('title', $episode->title ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700">
        </div>

        <div>
            <label class="block text-sm text-gray-400 mb-1">Description</label>
            <textarea name="description" rows="3" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700">{{ old('description', $episode->description ?? '') }}</textarea>
        </div>

        <div class="flex items-center space-x-4">
            <label class="flex items-center space-x-2 text-sm">
                <input type="checkbox" name="has_sub" value="1" @checked(old('has_sub', $episode->has_sub ?? true)) class="rounded bg-gray-800 border-gray-700 text-purple-600">
                <span>Sub</span>
            </label>
            <label class="flex items-center space-x-2 text-sm">
                <input type="checkbox" name="has_dub" value="1" @checked(old('has_dub', $episode->has_dub ?? false)) class="rounded bg-gray-800 border-gray-700 text-purple-600">
                <span>Dub</span>
            </label>
        </div>

        <div>
            <label class="block text-sm text-gray-400 mb-1">Air Date</label>
            <input type="date" name="air_date" value="{{ old('air_date', $episode->air_date ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700">
        </div>

        <div class="border-t border-gray-800 pt-4">
            <label class="block text-sm text-gray-400 mb-3 font-semibold">Video Source</label>

            <div class="flex space-x-1 mb-4 bg-gray-800 rounded-lg p-1">
                <button type="button" @click="tab = 'upload'; sourceType = 'upload'" :class="tab === 'upload' ? 'bg-purple-600 text-white' : 'text-gray-400 hover:text-white'" class="px-4 py-2 rounded-md text-sm font-medium transition">Upload File</button>
                <button type="button" @click="tab = 'url'; sourceType = 'direct_url'" :class="tab === 'url' ? 'bg-purple-600 text-white' : 'text-gray-400 hover:text-white'" class="px-4 py-2 rounded-md text-sm font-medium transition">Direct URL</button>
                <button type="button" @click="tab = 'youtube'; sourceType = 'youtube'" :class="tab === 'youtube' ? 'bg-purple-600 text-white' : 'text-gray-400 hover:text-white'" class="px-4 py-2 rounded-md text-sm font-medium transition">YouTube</button>
                <button type="button" @click="tab = 'servers'; sourceType = 'servers'" :class="tab === 'servers' ? 'bg-purple-600 text-white' : 'text-gray-400 hover:text-white'" class="px-4 py-2 rounded-md text-sm font-medium transition">External Servers</button>
            </div>
            <input type="hidden" name="source_type" x-model="sourceType">

            <div class="mb-4" x-show="tab !== 'servers'">
                <label class="block text-sm text-gray-400 mb-1">Language</label>
                <select name="language" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700">
                    @foreach($languages as $lang)
                    <option value="{{ $lang }}" {{ old('language', $episode->servers->first()->language ?? 'english') === $lang ? 'selected' : '' }}>{{ ucfirst($lang) }}</option>
                    @endforeach
                </select>
            </div>

            <div x-show="tab === 'upload'" class="space-y-4">
                <div class="border-2 border-dashed border-gray-700 rounded-lg p-8 text-center"
                     x-on:dragover.prevent="$el.classList.add('border-purple-500')"
                     x-on:dragleave.prevent="$el.classList.remove('border-purple-500')"
                     x-on:drop.prevent="handleDrop($event)">
                    <input type="file" name="video" accept="video/*" class="hidden" x-ref="fileInput" @change="handleFileSelect">
                    <template x-if="!uploadFile">
                        <div>
                            <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            <p class="text-gray-400 text-sm">Drag & drop a video file here, or <button type="button" @click="$refs.fileInput.click()" class="text-purple-500 hover:text-purple-400">browse</button></p>
                            <p class="text-gray-600 text-xs mt-1">MP4, WebM, MKV supported</p>
                        </div>
                    </template>
                    <template x-if="uploadFile">
                        <div>
                            <p class="text-white font-medium" x-text="uploadFile.name"></p>
                            <p class="text-gray-400 text-sm" x-text="formatSize(uploadFile.size)"></p>
                            <button type="button" @click="uploadFile = null; $refs.fileInput.value = ''" class="text-red-500 text-sm mt-2">Remove</button>
                            <div class="mt-3" x-show="uploading">
                                <div class="flex items-center justify-between text-sm text-gray-400 mb-1">
                                    <span>Uploading...</span>
                                    <span x-text="uploadProgress + '%'"></span>
                                </div>
                                <div class="bg-gray-700 rounded-full h-2.5 overflow-hidden">
                                    <div class="bg-gradient-to-r from-purple-600 to-purple-400 h-full rounded-full transition-all duration-200 ease-out" :style="'width: ' + uploadProgress + '%'"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <input type="hidden" name="storage_disk" x-model="storageDisk">

            <div x-show="tab === 'url'" class="space-y-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Video URL</label>
                    <input type="text" name="video_path" value="{{ old('video_path', $episode->video_path ?? '') }}" placeholder="https://..." class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Storage Disk</label>
                    <select x-model="storageDisk" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700">
                        <option value="local">Local</option>
                        <option value="s3">S3</option>
                        <option value="streaming">Streaming API</option>
                    </select>
                </div>
            </div>

            <div x-show="tab === 'youtube'" class="space-y-4">
                <p class="text-sm text-gray-500">Enter a YouTube URL to auto-fetch video details. This creates an embedded YouTube player.</p>
                <div class="flex space-x-2">
                    <input type="url" name="youtube_url" x-model="youtubeUrl" placeholder="https://youtube.com/watch?v=..." class="flex-1 bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700" @input="youtubePreview=null; previewError=null">
                    <button type="button" @click="previewYouTube" :disabled="!youtubeUrl" class="bg-purple-600 hover:bg-purple-700 disabled:opacity-50 text-white px-4 py-2 rounded-lg text-sm">Preview</button>
                </div>
                <template x-if="previewError">
                    <div class="bg-red-600/20 border border-red-600 text-red-400 text-sm rounded-lg p-3" x-text="previewError"></div>
                </template>
                <template x-if="youtubePreview">
                    <div class="bg-gray-800 rounded-lg p-4 mt-2">
                        <div class="aspect-video bg-black rounded mb-3">
                            <iframe :src="'https://www.youtube.com/embed/' + youtubePreview.id" class="w-full h-full rounded" allowfullscreen></iframe>
                        </div>
                        <p class="text-white font-semibold" x-text="youtubePreview.title"></p>
                        <p class="text-gray-400 text-sm" x-text="youtubePreview.author"></p>
                    </div>
                </template>
            </div>

            <div x-show="tab === 'servers'" class="space-y-4">
                <p class="text-sm text-gray-500">Add external video servers (these play as fallback/alternative sources).</p>
                <template x-for="(server, i) in servers" :key="i">
                    <div class="grid grid-cols-4 gap-2 items-start">
                        <input type="text" name="server_label[]" x-model="server.label" placeholder="Label" class="bg-gray-800 text-white rounded-lg px-3 py-2 text-sm border border-gray-700">
                        <input type="url" name="server_url[]" x-model="server.url" placeholder="URL" class="bg-gray-800 text-white rounded-lg px-3 py-2 text-sm border border-gray-700">
                        <select name="server_type[]" x-model="server.type" class="bg-gray-800 text-white rounded-lg px-3 py-2 text-sm border border-gray-700">
                            <option value="mp4">MP4</option>
                            <option value="m3u8">HLS</option>
                            <option value="embed">Embed</option>
                            <option value="youtube">YouTube</option>
                        </select>
                        <div class="flex space-x-1">
                            <select name="server_language[]" x-model="server.language" class="flex-1 bg-gray-800 text-white rounded-lg px-3 py-2 text-sm border border-gray-700">
                                <option value="english">English</option>
                                <option value="japanese">Japanese</option>
                                <option value="hindi">Hindi</option>
                            </select>
                            <button type="button" @click="servers.splice(i, 1)" class="text-red-500 hover:text-red-400 px-2 py-2" title="Remove">&times;</button>
                        </div>
                    </div>
                </template>
                <button type="button" @click="servers.push({})" class="text-purple-500 text-sm">+ Add Server</button>
            </div>

            <div class="mt-4">
                <label class="block text-sm text-gray-400 mb-1">Thumbnail</label>
                <input type="file" name="thumbnail" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-gray-800 file:text-white">
            </div>
        </div>

        <input type="hidden" name="uploaded_video_path" x-model="uploadedPath">

        <div class="flex space-x-3">
            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg">
                <span x-show="!uploading">{{ isset($episode) ? 'Update' : 'Create' }} Episode</span>
                <span x-show="uploading">Uploading...</span>
            </button>
            @if(isset($episode) && $episode->video_path && $episode->storage_disk === 'local')
                <button type="button" onclick="if(confirm('Delete the uploaded video file?')) { document.getElementById('delete-video-form').submit(); }" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg">
                    Delete Video
                </button>
            @endif
        </div>
    </form>

    @if(isset($episode) && $episode->video_path && $episode->storage_disk === 'local')
        <form id="delete-video-form" action="{{ route('admin.anime.episodes.delete-video', [$anime, $episode]) }}" method="POST" class="hidden">
            @csrf @method('DELETE')
        </form>
    @endif
</div>

<script>
function episodeForm() {
    return {
        tab: 'upload',
        sourceType: 'upload',
        storageDisk: '{{ old('storage_disk', $episode->storage_disk ?? 'local') }}',
        uploadFile: null,
        uploadProgress: 0,
        uploading: false,
        uploadedPath: '',
        youtubeUrl: '',
        youtubePreview: null,
        previewError: null,
        servers: [],

        init() {
            const params = new URLSearchParams(window.location.search);
            if (params.get('source') === 'youtube') { this.tab = 'youtube'; this.sourceType = 'youtube'; }
            @if(isset($episode))
                @foreach($episode->servers as $server)
                    this.servers.push({ label: '{{ $server->label }}', url: '{{ $server->url }}', type: '{{ $server->type }}', language: '{{ $server->language }}' });
                @endforeach
                @if($episode->source_type === 'youtube')
                    this.tab = 'youtube'; this.sourceType = 'youtube';
                @elseif($episode->source_type === 'direct_url' || ($episode->video_path && $episode->storage_disk !== 'local'))
                    this.tab = 'url'; this.sourceType = 'direct_url';
                @elseif($episode->servers->count() > 0)
                    this.tab = 'servers'; this.sourceType = 'servers';
                @endif
            @endif
        },

        handleDrop(event) {
            event.preventDefault();
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                this.uploadFile = files[0];
                this.$refs.fileInput.files = files;
            }
        },

        handleFileSelect(event) {
            if (event.target.files.length > 0) {
                this.uploadFile = event.target.files[0];
                this.uploadedPath = '';
            }
        },

        formatSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        submitForm() {
            if (this.uploadFile && !this.uploadedPath) {
                this.uploadVideo();
            } else {
                this.$el.submit();
            }
        },

        uploadVideo() {
            this.uploading = true;
            this.uploadProgress = 0;

            const form = this.$el;
            const formData = new FormData();
            formData.append('file', this.uploadFile);
            formData.append('anime_slug', '{{ $anime->slug }}');

            const xhr = new XMLHttpRequest();

            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    this.uploadProgress = Math.round((e.loaded / e.total) * 100);
                }
            });

            xhr.addEventListener('load', () => {
                if (xhr.status >= 200 && xhr.status < 300) {
                    const data = JSON.parse(xhr.responseText);
                    this.uploadedPath = data.path;
                    this.uploadProgress = 100;
                    this.uploading = false;
                    this.uploadFile = null;
                    this.$refs.fileInput.value = '';
                    form.submit();
                } else {
                    this.uploading = false;
                    this.uploadProgress = 0;
                    alert('Upload failed. Please try again.');
                }
            });

            xhr.addEventListener('error', () => {
                this.uploading = false;
                this.uploadProgress = 0;
                alert('Upload failed. Network error.');
            });

            xhr.open('POST', '/admin/upload/file');
            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
            xhr.send(formData);
        },

        previewYouTube() {
            this.previewError = null;
            this.youtubePreview = null;
            const form = this.$el.closest('form');
            const animeId = {{ $anime->id }};
            const epNum = form.querySelector('input[name="number"]').value;

            fetch('/admin/youtube/preview', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    url: this.youtubeUrl,
                    anime_id: animeId,
                    episode_number: epNum || null,
                })
            })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                if (ok && data.id) {
                    this.youtubePreview = data;
                } else {
                    this.previewError = data.error || 'Could not fetch video info. Check the URL and try again.';
                }
            })
            .catch(() => {
                this.previewError = 'Network error. Make sure the server is running.';
            });
        }
    };
}
</script>
@endsection
