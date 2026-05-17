@extends('admin.layouts.app')

@section('title', 'Featured Slider')

@section('content')
<div class="max-w-4xl">
    <h1 class="text-2xl font-bold mb-6">Featured Slider</h1>

    <div x-data="featuredManager()" x-init="init()" class="space-y-6">
        <div class="bg-gray-900 rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Auto-Fill</h2>
            <p class="text-sm text-gray-400 mb-4">Automatically populate the slider from existing anime.</p>
            <div class="flex flex-wrap items-center gap-3 mb-4">
                <form method="POST" action="{{ route('admin.featured.auto-fill') }}" class="inline">
                    @csrf
                    <input type="hidden" name="mode" value="top_rated">
                    <input type="hidden" name="count" value="5">
                    <button type="submit" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-500 text-white text-sm rounded-lg transition font-medium">Top Rated</button>
                </form>
                <form method="POST" action="{{ route('admin.featured.auto-fill') }}" class="inline">
                    @csrf
                    <input type="hidden" name="mode" value="most_viewed">
                    <input type="hidden" name="count" value="5">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm rounded-lg transition font-medium">Most Popular</button>
                </form>
                <form method="POST" action="{{ route('admin.featured.auto-fill') }}" class="inline">
                    @csrf
                    <input type="hidden" name="mode" value="recent">
                    <input type="hidden" name="count" value="5">
                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white text-sm rounded-lg transition font-medium">Recent Uploads</button>
                </form>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.featured.update') }}" id="featuredForm">
            @csrf

            <div class="bg-gray-900 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">Current Featured Anime</h2>
                    <span class="text-sm text-gray-400" x-text="items.length + ' selected'"></span>
                </div>

                <template x-if="items.length === 0">
                    <div class="text-center py-12 text-gray-500">
                        <p class="text-lg mb-2">No anime selected</p>
                        <p class="text-sm">Use auto-fill above or add anime manually below.</p>
                    </div>
                </template>

                <div class="space-y-2 mb-4">
                    <template x-for="(item, index) in items" :key="item.id">
                        <div class="flex items-center gap-3 bg-gray-800 rounded-lg px-4 py-3 group" :data-id="item.id">
                            <div class="flex flex-col gap-0.5">
                                <button @click="moveUp(index)" :disabled="index === 0" class="text-gray-500 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition" type="button">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                </button>
                                <button @click="moveDown(index)" :disabled="index === items.length - 1" class="text-gray-500 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition" type="button">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </div>
                            <img :src="item.thumbnail || 'https://via.placeholder.com/40x56/1a1a2e/7c3aed'" class="w-10 h-14 object-cover rounded flex-shrink-0" alt="">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-white truncate" x-text="item.title"></p>
                                <p class="text-xs text-gray-500" x-text="'Slide ' + (index + 1)"></p>
                            </div>
                            <button @click="removeItem(index)" class="text-gray-600 hover:text-red-400 transition opacity-0 group-hover:opacity-100" type="button">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            <input type="hidden" name="featured_ids[]" :value="item.id">
                        </div>
                    </template>
                </div>

                <div class="relative">
                    <div class="relative">
                        <input type="text" x-model="search" @focus="open = true" @keydown.escape="open = false" @keydown.enter="if(open && filteredList.length > 0) { addItem(filteredList[0]); search = ''; }" placeholder="Search anime to add..." class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <div x-show="open && search.length > 0" @click.outside="open = false" class="absolute top-full left-0 right-0 mt-1 bg-gray-800 border border-gray-700 rounded-lg max-h-60 overflow-y-auto z-50 shadow-xl" style="display: none;">
                            <template x-for="anime in filteredList" :key="anime.id">
                                <button @click="addItem(anime); search = ''; open = false" type="button" class="flex items-center gap-3 w-full px-4 py-2.5 text-left hover:bg-gray-700 text-sm transition" :class="isInItems(anime.id) ? 'opacity-40 cursor-not-allowed' : ''" :disabled="isInItems(anime.id)">
                                    <img :src="anime.thumbnail || 'https://via.placeholder.com/28x40/1a1a2e/7c3aed'" class="w-7 h-10 object-cover rounded flex-shrink-0" alt="">
                                    <span x-text="anime.title" class="text-gray-200 truncate"></span>
                                    <span x-show="isInItems(anime.id)" class="text-xs text-gray-500 ml-auto">Already added</span>
                                </button>
                            </template>
                            <div x-show="filteredList.length === 0" class="px-4 py-3 text-sm text-gray-500 text-center">
                                No anime found
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <p class="text-sm text-gray-400">Slides shown: <span x-text="Math.min(items.length, 5)"></span> (max 5)</p>
                <button type="submit" class="px-6 py-3 bg-purple-600 hover:bg-purple-500 text-white rounded-lg font-semibold transition" :disabled="items.length === 0">
                    Save Featured Slider
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function featuredManager() {
        return {
            items: [],
            allAnime: [],
            search: '',
            open: false,

            init() {
                this.items = @json($featured->map(fn($a) => ['id' => $a->id, 'title' => $a->title, 'thumbnail' => $a->thumbnail]));
                this.allAnime = @json($animeList->map(fn($a) => ['id' => $a->id, 'title' => $a->title, 'thumbnail' => $a->thumbnail]));
            },

            get filteredList() {
                if (!this.search) return [];
                const s = this.search.toLowerCase();
                return this.allAnime.filter(a => a.title.toLowerCase().includes(s));
            },

            isInItems(id) {
                return this.items.some(i => i.id === id);
            },

            addItem(anime) {
                if (this.isInItems(anime.id)) return;
                this.items.push({ ...anime });
                if (this.items.length > 5) {
                    this.items = this.items.slice(0, 5);
                }
            },

            removeItem(index) {
                this.items.splice(index, 1);
            },

            moveUp(index) {
                if (index === 0) return;
                [this.items[index - 1], this.items[index]] = [this.items[index], this.items[index - 1]];
            },

            moveDown(index) {
                if (index === this.items.length - 1) return;
                [this.items[index], this.items[index + 1]] = [this.items[index + 1], this.items[index]];
            }
        };
    }
</script>
@endpush
