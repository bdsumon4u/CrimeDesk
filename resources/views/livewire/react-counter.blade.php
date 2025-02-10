<!-- Move comments component inside modal -->
<div>
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
    <div class="flex gap-4">
        <a class="inline-flex items-center cursor-pointer" wire:click="like">
            <span class="mr-2">
                <x-filament::icon :icon="$crime->user_vote > 0 ? 'heroicon-m-hand-thumb-up' : 'heroicon-o-hand-thumb-up'" class="w-5 h-5 text-gray-500 dark:text-gray-400" />
            </span>
            <span class="text-lg font-bold">{{ $crime->upvotes_count }}</span>
        </a>
        <a class="inline-flex items-center cursor-pointer" wire:click="dislike">
            <span class="mr-2">
                <x-filament::icon :icon="$crime->user_vote < 0 ? 'heroicon-m-hand-thumb-down' : 'heroicon-o-hand-thumb-down'" class="w-5 h-5 text-gray-500 dark:text-gray-400" />
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
    </div>
</div>
