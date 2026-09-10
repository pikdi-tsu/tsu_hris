<?php

namespace Modules\System\View\Components;

use Illuminate\View\Component;
use Modules\System\Models\MenuSidebar;
use Modules\System\Services\BreadcrumbService;
use Illuminate\Support\Facades\Route;

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
        ?string $icon = null,
        bool $breadcrumb = true,
        ?array $breadcrumbItems = null
    ) {
        $this->title = $title;

        if (empty($icon) || $icon === 'fas fa-circle') {
            $routeName = Route::currentRouteName() ?: (request()->route() ? request()->route()->getName() : null);
            $sidebarIcon = $routeName ? MenuSidebar::where('route', $routeName)->value('icon') : null;
            $this->icon = $sidebarIcon ?: ($icon ?: 'fas fa-circle');
        } else {
            $this->icon = $icon;
        }

        $this->showBreadcrumb = $breadcrumb;
        $this->breadcrumbItems = $breadcrumbItems ?? ($breadcrumb ? BreadcrumbService::generate() : []);
    }

    public function render()
    {
        return view('system::components.tsu-page-header');
    }
}
