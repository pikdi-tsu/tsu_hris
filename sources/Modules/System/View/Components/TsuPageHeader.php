<?php

namespace Modules\System\View\Components;

use Illuminate\View\Component;
use Modules\System\Services\BreadcrumbService;

/**
 * TSU Page Header Component
 *
 * Usage:
 *   <x-tsu-page-header
 *       title="Data Karyawan"
 *       icon="fas fa-users"
 *       :breadcrumb="true"
 *   >
 *       <x-slot name="actions">
 *           <button class="btn tsu-btn-create btn-sm">
 *               <i class="fas fa-plus"></i> Tambah
 *           </button>
 *       </x-slot>
 *   </x-tsu-page-header>
 */
class TsuPageHeader extends Component
{
    public string $title;
    public string $icon;
    public bool $showBreadcrumb;
    public array $breadcrumbItems;

    public function __construct(
        string $title = '',
        string $icon = 'fas fa-circle',
        bool $breadcrumb = true
    ) {
        $this->title = $title;
        $this->icon = $icon;
        $this->showBreadcrumb = $breadcrumb;
        $this->breadcrumbItems = $breadcrumb ? BreadcrumbService::generate() : [];
    }

    public function render()
    {
        return view('system::components.tsu-page-header');
    }
}
