<x-filament-panels::page>
    @commentsStyles
    @commentsScripts
    <div class="grid grid-cols-12 gap-4">
        {{-- Main --}}
        <div class="md:col-span-8">
            <section class="min-h-screen mb-12">
                @forelse ($crimes as $crime)
                    <div wire:key="thread-{{ $crime->id }}">
                        <livewire:CrimePost
                            :$crime
                            :key="'crime-' . $crime->id"
                        />
                    </div>
                @empty
                    <div class="text-center dark:text-slate-400 text-slate-600">There are no crimes to show.</div>
                @endforelse

                <div>
                    {{ $crimes->links() }}
                </div>

                {{-- <x-load-more-button
                    :perPage="$perPage"
                    :paginator="$crimes"
                    message="There are no more crimes to load, or you have scrolled too far."
                /> --}}
            </section>
        </div>
        {{-- Sidebar --}}
        <div class="md:col-span-4">
            @if (method_exists($this, 'filtersForm'))
                {{ $this->filtersForm }}
            @endif

            <x-filament-widgets::widgets
                :columns="$this->getColumns()"
                :data="
                    [
                        ...(property_exists($this, 'filters') ? ['filters' => $this->filters] : []),
                        ...$this->getWidgetData(),
                    ]
                "
                :widgets="$this->getVisibleWidgets()"
            />
        </div>
    </div>
</x-filament-panels::page>
