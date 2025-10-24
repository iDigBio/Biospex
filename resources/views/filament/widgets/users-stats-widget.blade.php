<div style="--col-span-lg: span 1 / span 1; min-height: 300px;" class="fi-wi-widget fi-grid-col lg:fi-grid-col-span">
    <section x-data="{ isCollapsed: false }" class="fi-section fi-section-has-header" data-has-alpine-state="true">
        <header class="fi-section-header">
            <div class="fi-section-header-text-ctn">
                <h2 class="fi-section-header-heading text-white">{{ $this->heading }}</h2>
            </div>
            <div class="fi-section-header-after-ctn">
                <div class="fi-input-wrp fi-wi-chart-filter">
                    <div wire:loading.delay.default.class.remove="ps-3" wire:target="filter"
                         class="fi-input-wrp-content-ctn fi-input-wrp-content-ctn-ps">
                        <select wire:model.live="filter"
                                class="fi-select-input fi-select-input-has-inline-prefix bg-gray-800 text-white border-gray-700 rounded-md">
                            @foreach ($filters as $value => $label)
                                <option value="{{ $value }}"
                                        {{ $activeFilter === $value ? 'selected' : '' }} class="bg-gray-800 text-white">
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </header>
        <div class="fi-section-content-ctn flex items-center justify-center h-full">
            <div class="fi-section-content text-center">
                <div style="font-size: 5rem; line-height: 1;" class="font-bold">
                    {{ $count }}
                </div>
            </div>
        </div>
    </section>
</div>