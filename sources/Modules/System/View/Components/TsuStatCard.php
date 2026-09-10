<?php

namespace Modules\System\View\Components;

use Illuminate\View\Component;

/**
 * TSU Stat Card Component
 *
 * Usage:
 *   <x-tsu-stat-card
 *       label="Total Karyawan"
 *       :value="$totalKaryawan"
 *       icon="fas fa-users"
 *       variant="primary"
 *       sublabel="Per hari ini"
 *   />
 *
 * Variants: primary | success | warning | danger | info
 */
class TsuStatCard extends Component
{
    public string $label;
    public mixed $value;
    public string $icon;
    public string $variant;
    public string $sublabel;

    public function __construct(
        string $label = '',
        mixed $value = 0,
        string $icon = 'fas fa-chart-bar',
        string $variant = 'primary',
        string $sublabel = ''
    ) {
        $this->label = $label;
        $this->value = $value;
        $this->icon = $icon;
        $this->variant = in_array($variant, ['primary', 'success', 'warning', 'danger', 'info'])
            ? $variant
            : 'primary';
        $this->sublabel = $sublabel;
    }

    public function render()
    {
        return view('system::components.tsu-stat-card');
    }
}
