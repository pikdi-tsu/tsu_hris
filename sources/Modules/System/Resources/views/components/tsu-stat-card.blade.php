{{--
    TSU Stat Card Component View
    Props: $label, $value, $icon, $variant, $sublabel
    Variants: primary | success | warning | danger | info
--}}
<div class="card tsu-stat-card tsu-stat-{{ $variant }} mb-0">
    <div class="card-body">
        <div class="tsu-stat-card__label">{{ $label }}</div>
        <div class="tsu-stat-card__value">{{ $value }}</div>
        @if($sublabel)
            <div class="tsu-stat-card__sublabel">{{ $sublabel }}</div>
        @endif
        <i class="{{ $icon }} tsu-stat-card__icon"></i>
    </div>
</div>
