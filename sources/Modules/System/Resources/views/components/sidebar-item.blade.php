{{--
    FILE: Modules/System/Resources/views/layouts/sidebar-item.blade.php
    Merender menu secara berulang (Recursive)
--}}

@php
    // CEK PERMISSION
    if ($menu->permission_name && !auth()->user()->can($menu->permission_name)) {
        return;
    }

    // CUSTOM LOGIC: Hide MPP from non-atasan
    if ($menu->route === 'users.mpp.index') {
        $user = auth()->user();
        // Bypass untuk super admin & admin
        if (!$user->hasRole(['super admin', 'super admin hris', 'admin', 'admin hris'])) {
            $profile = \App\Models\DataDosenTendik::where('user_id', $user->id)->first();
            if (!$profile) return;

            $jabatanStrukturalIds = \App\Models\KaryawanJabatanStruktural::where('data_dosen_tendik_id', $profile->id)
                ->where('is_active', 'Y')
                ->pluck('jabatan_struktural_id');

            $isAtasan = \App\Models\MasterUnit::whereIn('kepala_jabatan_id', $jabatanStrukturalIds)->exists();
            if (!$isAtasan) {
                return;
            }
        }
    }

    // CUSTOM LOGIC: Hide Approval Cuti & Approval Izin from non-atasan / non-HRD
    if (in_array($menu->route, ['users.indexapprovalcuti', 'users.indexapprovalizin'])) {
        $user = auth()->user();
        if (!$user->hasRole(['super admin', 'super admin hris', 'admin', 'admin hris'])) {
            $profile = \App\Models\DataDosenTendik::where('user_id', $user->id)->first();
            if (!$profile) return;

            $isHrd = str_contains(strtoupper($profile->posisi ?? ''), 'SDM') || str_contains(strtoupper($profile->posisi ?? ''), 'SUMBER DAYA MANUSIA');

            $jabatanStrukturalIds = \App\Models\KaryawanJabatanStruktural::where('data_dosen_tendik_id', $profile->id)
                ->whereIn('is_active', [1, '1', 'Y', 'y'])
                ->pluck('jabatan_struktural_id');

            $isAtasan = \App\Models\MasterUnit::whereIn('kepala_jabatan_id', $jabatanStrukturalIds)->exists();

            $hasAssignedApproval = \App\Models\CutiKaryawan::where('id_atasan', $profile->id)->orWhere('id_hrd', $profile->id)->exists()
                || \App\Models\IzinKaryawan::where('id_atasan', $profile->id)->orWhere('id_hrd', $profile->id)->exists();

            if (!$isHrd && !$isAtasan && !$hasAssignedApproval) {
                return;
            }
        }
    }

    // Filter Children
    $visibleChildren = $menu->children->filter(function ($child) {
        return empty($child->permission_name) || auth()->user()->can($child->permission_name);
    });

    $hasChildren = $visibleChildren->isNotEmpty();

    // Logic Parent kosong (Route '#' atau kosong)
    $isFolder = empty($menu->route) || $menu->route === '#';

    if ($isFolder && !$hasChildren) {
        return;
    }

    // Cek Status Aktif
    $isActive = $menu->isActive();

    // Hirarki
    $currentLevel = isset($level) ? $level : 0;
    $paddingLeft  = 0.8 + ($currentLevel * 1.0);

    // Menu indikator
    $indicator = $hasChildren ? 'fas fa-chevron-right' : 'fas fa-minus';

    // Default Icon Menu
    $mainIcon = $menu->icon ?: 'fas fa-box';

    // Default-nya href
    $href = '#';

    if (! $hasChildren && Route::has($menu->route)) {
        $href = route($menu->route);
    }
@endphp

<li class="nav-item {{ $hasChildren && $isActive ? 'menu-open' : '' }}">

    <a href="{{ $href }}"
       class="nav-link {{ $isActive ? 'active' : '' }}"
       style="padding-left: {{ $paddingLeft }}rem !important; display: flex; align-items: center;">
        <i class="nav-indicator {{ $indicator }} mr-2"></i>
        <i class="nav-icon {{ $mainIcon }} mr-2"></i>
        <p class="mb-0" style="flex: 1;">
            {{ $menu->name }}
        </p>
    </a>

    @if($hasChildren)
        <ul class="nav nav-treeview">
            @foreach ($visibleChildren as $child)
                @include('system::components.sidebar-item', ['menu' => $child, 'level' => $currentLevel + 1])
            @endforeach
        </ul>
    @endif
</li>
