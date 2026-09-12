<div class="card border-0 shadow-sm mb-4 tsu-master-guide" style="border-left: 4px solid #4e73df !important; border-radius: 8px; background-color: #f8faff;">
    <style>
        .tsu-master-guide .card-header::after,
        .tsu-master-guide .card-header::before {
            display: none !important;
            content: none !important;
        }
        .tsu-master-guide .guide-toggle-btn {
            margin-left: auto !important;
            color: #64748b;
            text-decoration: none !important;
            transition: transform 0.2s ease, color 0.2s ease;
        }
        .tsu-master-guide .guide-toggle-btn:hover {
            color: #094b54;
        }
        .tsu-master-guide [aria-expanded="false"] .guide-toggle-icon {
            transform: rotate(-90deg);
        }
    </style>
    <div class="card-header bg-transparent py-2 px-3 border-0 d-flex justify-content-between align-items-center w-100" style="cursor: pointer;" @if($collapsible) data-toggle="collapse" data-target="#{{ $guideId }}" aria-expanded="true" @endif>
        <div class="d-flex align-items-center">
            <span class="p-2 rounded-circle mr-2 text-primary" style="background: rgba(78, 115, 223, 0.1);">
                <i class="fas fa-network-wired"></i>
            </span>
            <div>
                <h6 class="font-weight-bold text-dark mb-0" style="font-size: 14px;">
                    {{ $title }}
                </h6>
                <small class="text-muted" style="font-size: 11px;">Peta Keterkaitan & Dampak Master Data</small>
            </div>
        </div>
        @if($collapsible)
            <button type="button" class="btn btn-sm btn-link text-muted p-0 guide-toggle-btn" title="Buka / Tutup Panduan">
                <i class="fas fa-chevron-down guide-toggle-icon"></i>
            </button>
        @endif
    </div>

    <div id="{{ $guideId }}" class="collapse show">
        <div class="card-body pt-1 pb-3 px-3">
            @if(!empty($description))
                <p class="text-secondary small mb-2" style="line-height: 1.5;">
                    {{ $description }}
                </p>
            @endif

            @if(!empty($connections) && count($connections) > 0)
                <div class="mb-2">
                    <span class="small font-weight-bold text-dark mr-2">
                        <i class="fas fa-link text-primary mr-1"></i> Tersambung ke Modul Operasional:
                    </span>
                    <div class="d-inline-flex flex-wrap align-items-center mt-1" style="gap: 6px;">
                        @foreach($connections as $conn)
                            @php
                                $href = '#';
                                if (isset($conn['route']) && \Illuminate\Support\Facades\Route::has($conn['route'])) {
                                    $href = route($conn['route']);
                                } elseif (isset($conn['url'])) {
                                    $href = url($conn['url']);
                                }
                                $icon = $conn['icon'] ?? 'fas fa-external-link-alt';
                                $badgeClass = $conn['badge'] ?? 'badge-light-primary text-primary border border-primary';
                            @endphp

                            @if($href !== '#')
                                <a href="{{ $href }}" class="badge {{ $badgeClass }} px-2 py-1 text-decoration-none shadow-xs" style="font-size: 11.5px; transition: all 0.2s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'" title="Buka modul {{ $conn['label'] }}">
                                    <i class="{{ $icon }} mr-1"></i> {{ $conn['label'] }}
                                </a>
                            @else
                                <span class="badge {{ $badgeClass }} px-2 py-1" style="font-size: 11.5px;">
                                    <i class="{{ $icon }} mr-1"></i> {{ $conn['label'] }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!empty($impact))
                <div class="p-2 rounded mt-2 border" style="background-color: #ffffff; border-color: #e3e6f0 !important;">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-info-circle text-info mt-1 mr-2" style="font-size: 13px;"></i>
                        <div class="small text-muted" style="line-height: 1.4;">
                            <strong class="text-dark">Catatan Dampak:</strong> {{ $impact }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
