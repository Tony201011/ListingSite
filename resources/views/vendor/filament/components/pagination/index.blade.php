@props([
    'currentPageOptionProperty' => 'tableRecordsPerPage',
    'extremeLinks' => false,
    'paginator',
    'pageOptions' => [],
])

@php
    use Illuminate\Contracts\Pagination\CursorPaginator;
    use Illuminate\Support\Str;

    $isRtl = __('filament-panels::layout.direction') === 'rtl';
    $isSimple = ! $paginator instanceof \Illuminate\Pagination\LengthAwarePaginator;

    $currentPath = request()->path();
    $previousPath = ltrim(parse_url(url()->previous(), PHP_URL_PATH) ?? '', '/');

    $isProvidersPage = Str::is('admin/providers*', $currentPath)
        || Str::is('admin/providers*', $previousPath);
@endphp

<nav
    aria-label="{{ __('filament::components/pagination.label') }}"
    role="navigation"
    {{
        $attributes->class([
            'fi-pagination',
            'fi-simple' => $isSimple,
        ])
    }}
>
    @if ($isProvidersPage)
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; align-items: center; width: 100%; gap: 16px;">
            <div style="display: flex; align-items: center; justify-content: flex-start; gap: 8px;">
                <span>Go to:</span>

                @if (! $isSimple && $paginator->lastPage() > 1)
                    <select
                        wire:change="gotoPage($event.target.value, '{{ $paginator->getPageName() }}')"
                        style="height: 30px; width: 70px;"
                    >
                        @for ($page = 1; $page <= $paginator->lastPage(); $page++)
                            <option value="{{ $page }}" @selected($page === $paginator->currentPage())>
                                {{ $page }}
                            </option>
                        @endfor
                    </select>
                @endif
            </div>

            <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                @if (count($pageOptions) > 1)
                    <span>Show rows:</span>

                    <select
                        wire:model.live="{{ $currentPageOptionProperty }}"
                        style="height: 30px; width: 70px;"
                    >
                        @foreach ($pageOptions as $option)
                            <option value="{{ $option }}">
                                {{ $option === 'all'
                                    ? __('filament::components/pagination.fields.records_per_page.options.all')
                                    : $option }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px;">
                @if (! $isSimple)
                    <span>
                        {{ \Illuminate\Support\Number::format($paginator->firstItem() ?? 0) }}
                        -
                        {{ \Illuminate\Support\Number::format($paginator->lastItem() ?? 0) }}
                        of
                        {{ \Illuminate\Support\Number::format($paginator->total()) }}
                    </span>
                @endif

                @php
                    $previousAction = $paginator instanceof CursorPaginator && ! $paginator->onFirstPage()
                        ? "setPage('{$paginator->previousCursor()->encode()}', '{$paginator->getCursorName()}')"
                        : "previousPage('{$paginator->getPageName()}')";

                    $nextAction = $paginator instanceof CursorPaginator && $paginator->hasMorePages()
                        ? "setPage('{$paginator->nextCursor()->encode()}', '{$paginator->getCursorName()}')"
                        : "nextPage('{$paginator->getPageName()}')";
                @endphp

                <button
                    type="button"
                    @if (! $paginator->onFirstPage())
                        wire:click="{{ $previousAction }}"
                    @else
                        disabled
                    @endif
                >
                    ‹
                </button>

                <button
                    type="button"
                    @if ($paginator->hasMorePages())
                        wire:click="{{ $nextAction }}"
                    @else
                        disabled
                    @endif
                >
                    ›
                </button>
            </div>
        </div>
    @else
        {{-- Default Filament Pagination --}}
        @if (! $paginator->onFirstPage())
            @php
                if ($paginator instanceof CursorPaginator) {
                    $wireClickAction = "setPage('{$paginator->previousCursor()->encode()}', '{$paginator->getCursorName()}')";
                } else {
                    $wireClickAction = "previousPage('{$paginator->getPageName()}')";
                }
            @endphp

            <x-filament::button
                color="gray"
                rel="prev"
                :wire:click="$wireClickAction"
                :wire:key="$this->getId() . '.pagination.previous'"
                class="fi-pagination-previous-btn"
            >
                {{ __('filament::components/pagination.actions.previous.label') }}
            </x-filament::button>
        @endif

        @if (! $isSimple)
            <span class="fi-pagination-overview">
                {{
                    trans_choice(
                        'filament::components/pagination.overview',
                        $paginator->total(),
                        [
                            'first' => \Illuminate\Support\Number::format($paginator->firstItem() ?? 0),
                            'last' => \Illuminate\Support\Number::format($paginator->lastItem() ?? 0),
                            'total' => \Illuminate\Support\Number::format($paginator->total()),
                        ],
                    )
                }}
            </span>
        @endif

        @if (count($pageOptions) > 1)
            <div class="fi-pagination-records-per-page-select-ctn">
                <label class="fi-pagination-records-per-page-select fi-compact">
                    <x-filament::input.wrapper>
                        <x-filament::input.select
                            :wire:model.live="$currentPageOptionProperty"
                        >
                            @foreach ($pageOptions as $option)
                                <option value="{{ $option }}">
                                    {{ $option === 'all'
                                        ? __('filament::components/pagination.fields.records_per_page.options.all')
                                        : $option }}
                                </option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>

                    <span class="fi-sr-only">
                        {{ __('filament::components/pagination.fields.records_per_page.label') }}
                    </span>
                </label>

                <label class="fi-pagination-records-per-page-select">
                    <x-filament::input.wrapper
                        :prefix="__('filament::components/pagination.fields.records_per_page.label')"
                    >
                        <x-filament::input.select
                            :wire:model.live="$currentPageOptionProperty"
                        >
                            @foreach ($pageOptions as $option)
                                <option value="{{ $option }}">
                                    {{ $option === 'all'
                                        ? __('filament::components/pagination.fields.records_per_page.options.all')
                                        : $option }}
                                </option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </label>
            </div>
        @endif

        @if ($paginator->hasMorePages())
            @php
                if ($paginator instanceof CursorPaginator) {
                    $wireClickAction = "setPage('{$paginator->nextCursor()->encode()}', '{$paginator->getCursorName()}')";
                } else {
                    $wireClickAction = "nextPage('{$paginator->getPageName()}')";
                }
            @endphp

            <x-filament::button
                color="gray"
                rel="next"
                :wire:click="$wireClickAction"
                :wire:key="$this->getId() . '.pagination.next'"
                class="fi-pagination-next-btn"
            >
                {{ __('filament::components/pagination.actions.next.label') }}
            </x-filament::button>
        @endif

        @if ((! $isSimple) && $paginator->hasPages())
            <ol class="fi-pagination-items">
                @if (! $paginator->onFirstPage())
                    @if ($extremeLinks)
                        <x-filament::pagination.item
                            :aria-label="__('filament::components/pagination.actions.first.label')"
                            :icon="$isRtl ? \Filament\Support\Icons\Heroicon::ChevronDoubleRight : \Filament\Support\Icons\Heroicon::ChevronDoubleLeft"
                            rel="first"
                            :wire:click="'gotoPage(1, \'' . $paginator->getPageName() . '\')'"
                            :wire:key="$this->getId() . '.pagination.first'"
                        />
                    @endif

                    <x-filament::pagination.item
                        :aria-label="__('filament::components/pagination.actions.previous.label')"
                        :icon="$isRtl ? \Filament\Support\Icons\Heroicon::ChevronRight : \Filament\Support\Icons\Heroicon::ChevronLeft"
                        rel="prev"
                        :wire:click="'previousPage(\'' . $paginator->getPageName() . '\')'"
                        :wire:key="$this->getId() . '.pagination.previous'"
                    />
                @endif

                @foreach ($paginator->render()->offsetGet('elements') as $element)
                    @if (is_string($element))
                        <x-filament::pagination.item disabled :label="$element" />
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <x-filament::pagination.item
                                :active="$page === $paginator->currentPage()"
                                :aria-label="trans_choice('filament::components/pagination.actions.go_to_page.label', $page, ['page' => \Illuminate\Support\Number::format($page)])"
                                :label="\Illuminate\Support\Number::format($page)"
                                :wire:click="'gotoPage(' . $page . ', \'' . $paginator->getPageName() . '\')'"
                                :wire:key="$this->getId() . '.pagination.' . $paginator->getPageName() . '.' . $page"
                            />
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <x-filament::pagination.item
                        :aria-label="__('filament::components/pagination.actions.next.label')"
                        :icon="$isRtl ? \Filament\Support\Icons\Heroicon::ChevronLeft : \Filament\Support\Icons\Heroicon::ChevronRight"
                        rel="next"
                        :wire:click="'nextPage(\'' . $paginator->getPageName() . '\')'"
                        :wire:key="$this->getId() . '.pagination.next'"
                    />

                    @if ($extremeLinks)
                        <x-filament::pagination.item
                            :aria-label="__('filament::components/pagination.actions.last.label')"
                            :icon="$isRtl ? \Filament\Support\Icons\Heroicon::ChevronDoubleLeft : \Filament\Support\Icons\Heroicon::ChevronDoubleRight"
                            rel="last"
                            :wire:click="'gotoPage(' . $paginator->lastPage() . ', \'' . $paginator->getPageName() . '\')'"
                            :wire:key="$this->getId() . '.pagination.last'"
                        />
                    @endif
                @endif
            </ol>
        @endif
    @endif
</nav>
