{{--
    TSU Page Header Component View
    Props: $title, $icon, $showBreadcrumb, $breadcrumbItems
    Slots: $actions (optional — action buttons di kanan)
--}}
<div class="content-header tsu-page-header">
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            {{-- Kiri: Judul + Breadcrumb --}}
            <div>
                <h1 class="tsu-page-header__title">
                    <i class="{{ $icon }}"></i>
                    {{ $title }}
                </h1>
                @if($showBreadcrumb && count($breadcrumbItems) > 0)
                    <nav aria-label="breadcrumb" class="mt-1">
                        <ol class="breadcrumb tsu-breadcrumb mb-0">
                            @foreach($breadcrumbItems as $index => $item)
                                @if($index === count($breadcrumbItems) - 1 || $item['url'] === null)
                                    <li class="breadcrumb-item active" aria-current="page">
                                        {{ $item['label'] }}
                                    </li>
                                @else
                                    <li class="breadcrumb-item">
                                        <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                                    </li>
                                @endif
                            @endforeach
                        </ol>
                    </nav>
                @endif
            </div>

            {{-- Kanan: Action Buttons (slot) --}}
            @if(isset($actions))
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    {{ $actions }}
                </div>
            @endif
        </div>
    </div>
</div>
