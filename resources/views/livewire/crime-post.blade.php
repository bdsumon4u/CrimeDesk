<article>
    <div class="flex flex-col items-center px-8 mx-8 wrapper">
        <!-- Card-->
        <article class="flex flex-col w-full p-6 mb-4 bg-white break-inside rounded-xl dark:bg-slate-800 bg-clip-border">
            <div class="flex items-center justify-between pb-6">
                <div class="flex justify-between w-full gap-2">
                    <a class="flex gap-2" href="#">
                        <x-filament-panels::avatar.user :user="$crime->user" size="lg" :circular="false" />
                        <div class="font-bold text-md">
                            <div>{{ $crime->user->name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $crime->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </a>
                    <div class="text-slate-500 dark:text-slate-400">
                        <p title="{{ $crime->happened_at->format('H:i A') }}"><strong>Crime On:</strong>
                            {{ $crime->happened_at->format('d-M-Y') }}</p>
                        <p><strong>Verification Score:</strong> {{ $crime->score }}</p>
                    </div>
                </div>
            </div>
            <h2 class="text-3xl font-extrabold dark:text-white">
                {{ $crime->title }}
            </h2>
            <div class="pt-4">
                <div x-data="{
                    selectedMedia: null,
                    mediaItems: @js(
                        $crime->getMedia()->map(
                            fn($media) => [
                                'url' => $media->getUrl(),
                                'type' => str_starts_with($media->mime_type, 'video/') ? 'video' : 'image',
                                'thumbnail' => $media->getUrl(),
                            ],
                        )
                    ),
                    next() {
                        let idx = this.mediaItems.findIndex(item => item.url === this.selectedMedia.url);
                        if (idx < this.mediaItems.length - 1) {
                            this.selectedMedia = this.mediaItems[idx + 1];
                        }
                    },
                    prev() {
                        let idx = this.mediaItems.findIndex(item => item.url === this.selectedMedia.url);
                        if (idx > 0) {
                            this.selectedMedia = this.mediaItems[idx - 1];
                        }
                    }
                }">
                    @php
                        $mediaCount = $crime->getMedia()->count();
                    @endphp

                    <div class="grid grid-cols-6 gap-4 mb-4">
                        @foreach ($crime->getMedia() as $index => $media)
                            @php
                                $itemClasses = match (true) {
                                    $mediaCount === 1 => 'col-span-full',
                                    $mediaCount % 3 === 1 && $index >= $mediaCount - 4 => 'col-span-3',
                                    $mediaCount % 3 === 2 && $index >= $mediaCount - 2 => 'col-span-3',
                                    default => 'col-span-2',
                                };
                            @endphp

                            <div class="relative cursor-pointer aspect-square {{ $itemClasses }}"
                                x-on:click="selectedMedia = mediaItems[{{ $index }}]">
                                @if (str_starts_with($media->mime_type, 'video/'))
                                    <div class="relative w-full h-full">
                                        <video src="{{ $media->getUrl() }}"
                                            class="object-cover w-full h-full border rounded-md border-slate-200 dark:border-slate-700"
                                            preload="metadata"></video>
                                        <div
                                            class="absolute inset-0 flex items-center justify-center bg-black rounded-md bg-opacity-30">
                                            <svg class="w-12 h-12 text-white drop-shadow-lg" fill="currentColor"
                                                viewBox="0 0 24 24">
                                                <path d="M8 5v14l11-7z" />
                                            </svg>
                                        </div>
                                    </div>
                                @else
                                    <img src="{{ $media->getUrl() }}"
                                        class="object-cover w-full h-full border rounded-md border-slate-200 dark:border-slate-700" />
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Lightbox -->
                    <div x-show="selectedMedia" x-cloak
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75"
                        @click.self="selectedMedia = null" @keydown.escape.window="selectedMedia = null"
                        @keydown.arrow-right.window="next()" @keydown.arrow-left.window="prev()">

                        <div class="relative max-w-7xl max-h-[90vh] mx-auto p-4">
                            <template x-if="selectedMedia?.type === 'image'">
                                <img :src="selectedMedia?.url"
                                    class="max-h-[80vh] mx-auto rounded-md border border-slate-700" />
                            </template>
                            <template x-if="selectedMedia?.type === 'video'">
                                <video :src="selectedMedia?.url" controls
                                    class="max-h-[80vh] mx-auto rounded-md border border-slate-700"
                                    controlsList="nodownload" preload="auto" x-init="$el.play()"></video>
                            </template>

                            <!-- Navigation buttons -->
                            <button @click.stop="prev()"
                                class="absolute p-2 text-white transition-all -translate-y-1/2 bg-black bg-opacity-50 rounded-md left-4 top-1/2 hover:bg-opacity-75"
                                x-show="mediaItems.findIndex(item => item.url === selectedMedia?.url) > 0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <button @click.stop="next()"
                                class="absolute p-2 text-white transition-all -translate-y-1/2 bg-black bg-opacity-50 rounded-md right-4 top-1/2 hover:bg-opacity-75"
                                x-show="mediaItems.findIndex(item => item.url === selectedMedia?.url) < mediaItems.length - 1">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </button>

                            <!-- Close button -->
                            <button @click.stop="selectedMedia = null"
                                class="absolute p-2 text-white transition-all bg-black bg-opacity-50 rounded-md top-4 right-4 hover:bg-opacity-75">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="dark:text-slate-200" x-data="{
                    expanded: false,
                    isLong: false,
                    init() {
                        this.isLong = this.$refs.content.scrollHeight > 100;
                    }
                }">
                    <div x-ref="content" :class="!expanded && isLong ? 'line-clamp-3' : ''"
                        class="break-words transition-all duration-300 overflow-wrap-anywhere">
                        {!! $crime->description !!}
                    </div>

                    <button x-show="isLong" @click="expanded = !expanded"
                        class="mt-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">
                        <span x-text="expanded ? 'Show less' : 'Show more'"></span>
                    </button>
                </div>

                <div class="flex gap-4">
                    <a class="inline-flex items-center cursor-pointer" wire:click="like">
                        <span class="mr-2">
                            <x-filament::icon :icon="$crime->user_vote > 0 ? 'heroicon-m-hand-thumb-up' : 'heroicon-o-hand-thumb-up'" class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                        </span>
                        <span class="text-lg font-bold">{{ $crime->upvotes_count }}</span>
                    </a>
                    <a class="inline-flex items-center cursor-pointer" wire:click="dislike">
                        <span class="mr-2">
                            <x-filament::icon :icon="$crime->user_vote < 0
                                ? 'heroicon-m-hand-thumb-down'
                                : 'heroicon-o-hand-thumb-down'" class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                        </span>
                        <span class="text-lg font-bold">{{ $crime->downvotes_count }}</span>
                    </a>
                    <a class="inline-flex items-center cursor-pointer"
                        x-on:click="$dispatch('open-modal', { id: 'crime-comments-{{ $crime->id }}' })">
                        <span class="mr-2">
                            <x-filament::icon icon="heroicon-o-chat-bubble-left-right"
                                class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                        </span>
                        <span class="text-lg font-bold">{{ $crime->comments->count() }}</span>
                    </a>
                    <div class="grid ml-auto text-sm font-bold text-gray-500 dark:text-gray-400 place-content-center">
                        {{ $crime->district->name }}, {{ $crime->division->name }}
                    </div>
                </div>
            </div>

            <x-filament::modal id="crime-comments-{{ $crime->id }}" width="3xl">
                <x-slot name="header">
                    <div class="flex items-center gap-x-3">
                        <x-filament-panels::avatar.user :user="$crime->user" size="md" :circular="false" />
                        <div>
                            <h2 class="text-xl font-bold">{{ $crime->user->name }}</h2>
                            <p class="text-sm text-gray-500">{{ $crime->created_at->format('F d, Y') }}</p>
                        </div>
                    </div>
                </x-slot>

                <div class="space-y-2">
                    <h3 class="text-2xl font-bold">{{ $crime->title }}</h3>

                    <div class="border-t">
                        <x-comments::index :model="$crime" />
                    </div>
                </div>
            </x-filament::modal>
        </article>
    </div>
</article>
