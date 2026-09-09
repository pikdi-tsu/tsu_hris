@extends('system::template.admin.header')
@section('title', $title)

@section('content')
<style>
    /* Segmented Control Pill */
    .tsu-segmented-control {
        display: inline-flex;
        background: #e6f4f6;
        padding: 3px;
        border-radius: var(--tsu-radius);
        border: 1px solid var(--tsu-primary-light);
        gap: 3px;
    }
    .tsu-segmented-btn {
        border: none;
        background: transparent;
        color: var(--tsu-primary-dark);
        font-size: 0.82rem;
        font-weight: 600;
        padding: 0.35rem 0.85rem;
        border-radius: calc(var(--tsu-radius) - 2px);
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
    }
    .tsu-segmented-btn:hover:not(.active) {
        background: rgba(29, 122, 135, 0.1);
        color: var(--tsu-primary);
    }
    .tsu-segmented-btn.active {
        background: var(--tsu-primary);
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(9, 75, 84, 0.2);
    }

    /* Org Card TSU Style */
    .tsu-org-card {
        background: #ffffff;
        border: 1.5px solid var(--tsu-primary-light);
        border-radius: var(--tsu-radius-lg);
        box-shadow: var(--tsu-shadow);
        transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.22s ease, border-color 0.22s ease;
        cursor: pointer;
        overflow: hidden;
        position: relative;
        display: flex;
        flex-direction: column;
    }
    .tsu-org-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3.5px;
        background: linear-gradient(90deg, var(--tsu-primary-dark), var(--tsu-primary), var(--tsu-gold));
        transition: height 0.2s ease;
    }
    .tsu-org-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--tsu-shadow-md);
        border-color: var(--tsu-primary);
    }
    .tsu-org-card:hover::before {
        height: 5px;
    }

    /* Card Header */
    .tsu-org-card__header {
        padding: 1.15rem 1.15rem 0.5rem 1.15rem;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.5rem;
    }
    .tsu-org-card__title {
        font-size: 0.96rem;
        font-weight: 700;
        color: var(--tsu-primary-dark);
        line-height: 1.35;
        margin: 0;
        flex: 1;
        transition: color 0.2s ease;
    }
    .tsu-org-card:hover .tsu-org-card__title {
        color: var(--tsu-primary);
    }
    .tsu-org-card__badge-sub {
        background: var(--tsu-primary-faint);
        color: var(--tsu-primary-dark);
        border: 1px solid var(--tsu-primary-light);
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.2rem 0.55rem;
        border-radius: 20px;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    /* Card Body / Leader section */
    .tsu-org-card__body {
        padding: 0.5rem 1.15rem 0.85rem 1.15rem;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .tsu-org-leader-box {
        background: var(--tsu-primary-faint);
        border: 1px solid var(--tsu-primary-light);
        border-radius: var(--tsu-radius);
        padding: 0.65rem 0.85rem;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .tsu-org-leader-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--tsu-primary-dark), var(--tsu-primary));
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        flex-shrink: 0;
        box-shadow: 0 2px 4px rgba(9, 75, 84, 0.15);
    }
    .tsu-org-leader-avatar.empty {
        background: #e2e8f0;
        color: #94a3b8;
        box-shadow: none;
    }
    .tsu-org-leader-info {
        flex: 1;
        min-width: 0;
    }
    .tsu-org-leader-label {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        margin-bottom: 0.1rem;
    }
    .tsu-org-leader-name {
        font-size: 0.86rem;
        font-weight: 700;
        color: var(--tsu-primary-dark);
        margin-bottom: 0.2rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .tsu-org-leader-title {
        display: inline-block;
        font-size: 0.72rem;
        font-weight: 600;
        color: var(--tsu-primary);
        max-width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .tsu-org-leader-title.empty {
        color: #94a3b8;
        font-style: italic;
    }

    /* Stats bar: Pegawai & Kuota MPP */
    .tsu-org-stat-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.4rem 0.75rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: var(--tsu-radius);
        font-size: 0.78rem;
    }
    .tsu-org-stat-bar .stat-count {
        font-weight: 700;
        color: var(--tsu-primary-dark);
    }
    .tsu-org-stat-bar .stat-icon {
        color: var(--tsu-primary);
        margin-right: 0.35rem;
    }

    /* Card Footer Action */
    .tsu-org-card__footer {
        padding: 0.6rem 1.15rem 0.9rem 1.15rem;
        background: #ffffff;
        border-top: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .tsu-org-card__footer .btn-move {
        flex: 1;
        font-size: 0.78rem;
        font-weight: 600;
        border-radius: var(--tsu-radius);
        padding: 0.35rem 0.6rem;
        border: 1px solid var(--tsu-primary-light);
        color: var(--tsu-primary);
        background: var(--tsu-primary-faint);
        transition: all 0.2s ease;
    }
    .tsu-org-card__footer .btn-move:hover {
        background: var(--tsu-primary);
        color: #ffffff;
        border-color: var(--tsu-primary);
    }
    .tsu-org-card__footer .btn-drill {
        font-size: 0.78rem;
        font-weight: 600;
        border-radius: var(--tsu-radius);
        padding: 0.35rem 0.75rem;
        background: transparent;
        color: var(--tsu-primary-dark);
        border: 1px solid transparent;
        transition: all 0.2s ease;
    }
    .tsu-org-card:hover .tsu-org-card__footer .btn-drill {
        color: var(--tsu-primary);
    }

    /* Drilldown Breadcrumb TSU */
    .tsu-org-trail {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.4rem;
        background: #ffffff;
        border: 1.5px solid var(--tsu-primary-light);
        border-radius: var(--tsu-radius-lg);
        padding: 0.65rem 1rem;
        margin-bottom: 1.25rem;
        box-shadow: var(--tsu-shadow);
    }
    .tsu-org-trail-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.65rem;
        border-radius: var(--tsu-radius);
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--tsu-primary);
        background: var(--tsu-primary-faint);
        border: 1px solid var(--tsu-primary-light);
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }
    .tsu-org-trail-btn:hover {
        background: var(--tsu-primary);
        color: #ffffff;
    }
    .tsu-org-trail-active {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.65rem;
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--tsu-primary-dark);
        background: transparent;
    }
    .tsu-org-trail-sep {
        color: #94a3b8;
        font-size: 0.7rem;
    }

    /* Skeleton Loading Cards */
    .tsu-skeleton {
        background: linear-gradient(90deg, #f0f4f8 25%, #e2eaee 50%, #f0f4f8 75%);
        background-size: 200% 100%;
        animation: tsu-shimmer 1.4s infinite;
        border-radius: 4px;
    }
    @@keyframes tsu-shimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    .tsu-skeleton-card {
        background: #ffffff;
        border: 1.5px solid var(--tsu-primary-light);
        border-radius: var(--tsu-radius-lg);
        padding: 1.25rem;
        height: 220px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
</style>

{{-- TSU Page Header --}}
<x-tsu-page-header
    title="Struktur Organisasi"
    icon="fas fa-sitemap"
    :breadcrumb="true"
>
    <x-slot name="actions">
        <div class="tsu-segmented-control" id="org-mode-switch">
            <button type="button" class="tsu-segmented-btn active" id="btn-mode-card">
                <i class="fas fa-th-large mr-1"></i> Mode Kartu
            </button>
            <button type="button" class="tsu-segmented-btn" id="btn-mode-tree">
                <i class="fas fa-sitemap mr-1"></i> Mode Bagan (Full Tree)
            </button>
        </div>
        <button class="btn btn-sm tsu-btn-export ml-1" id="btn-export-img" style="display:none;">
            <i class="fas fa-download mr-1"></i> Download Bagan
        </button>
    </x-slot>
</x-tsu-page-header>

{{-- Main Content Section --}}
<section class="content">
    <div class="container-fluid">

        <!-- Breadcrumb Navigation for Org Chart (Drilldown) -->
        <div class="tsu-org-trail" id="org-breadcrumb" style="display: none;">
            <!-- Dinamis via JS -->
        </div>

        <!-- Section: Unit List (Cards) -->
        <div class="card card-primary card-outline tsu-card" id="card-unit-section">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-building mr-2" style="color:var(--tsu-primary);"></i>
                    <span id="current-unit-title">Tiga Serangkai Universitas (Unit Utama)</span>
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-reset" style="display: none;" onclick="loadCoreUnits()">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Awal
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row" id="unit-container">
                    <!-- Loading state via JS -->
                </div>
            </div>
        </div>

        <!-- Section: Karyawan & Jabatan (Tabel) -->
        <div class="card card-primary card-outline tsu-card mt-4" id="employee-section" style="display: none;">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-users mr-2" style="color:var(--tsu-primary);"></i>
                    Daftar Jabatan &amp; Karyawan di <span id="emp-unit-name" style="color:var(--tsu-primary-dark);"></span>
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover m-0" id="employee-table">
                        <thead style="background:var(--tsu-primary-faint);">
                            <tr style="font-size:.78rem;font-weight:700;color:var(--tsu-primary-dark);text-transform:uppercase;letter-spacing:.04em;">
                                <th width="5%" class="text-center">No</th>
                                <th width="30%">Nama Lengkap</th>
                                <th width="25%">Jabatan Struktural</th>
                                <th width="20%">Posisi Harian</th>
                                <th width="20%" class="text-center">Tipe Karyawan</th>
                            </tr>
                        </thead>
                        <tbody id="employee-tbody">
                            <!-- Dinamis via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Section: Full Tree Chart (Hidden by default) -->
        <div class="card card-primary card-outline tsu-card" id="full-tree-section" style="display: none;">
            <div class="card-body p-0" style="overflow: hidden; border-radius: 0 0 var(--tsu-radius-lg) var(--tsu-radius-lg);">
                <div id="chart-container" style="width: 100%; height: calc(100vh - 190px); background-color: #f8fbfc;"></div>
            </div>
        </div>

    </div>
</section>
@endsection

@section('script')
<script src="https://d3js.org/d3.v7.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/d3-org-chart@3.1.0"></script>
<script src="https://cdn.jsdelivr.net/npm/d3-flextree@2.1.2/build/d3-flextree.js"></script>

<script>
    // Menyimpan jejak navigasi (Breadcrumb)
    let navigationHistory = [];
    let allUnitsList = []; // Untuk dropdown pindah induk
    let chart = null; // Instance d3-org-chart

    $(document).ready(function() {
        loadCoreUnits();
        fetchAllUnits();

        // Toggle Mode Kartu
        $('#btn-mode-card').click(function() {
            $(this).addClass('active');
            $('#btn-mode-tree').removeClass('active');
            $('#full-tree-section').hide();
            $('#btn-export-img').hide();
            $('#card-unit-section').show();
            if($('#employee-tbody').find('tr').length > 1) {
                $('#employee-section').show();
            }
            if (navigationHistory.length > 0) {
                $('#org-breadcrumb').show();
            }
        });

        // Toggle Mode Bagan
        $('#btn-mode-tree').click(function() {
            $(this).addClass('active');
            $('#btn-mode-card').removeClass('active');
            $('#card-unit-section').hide();
            $('#employee-section').hide();
            $('#org-breadcrumb').hide();
            $('#full-tree-section').show();
            $('#btn-export-img').show();
            
            if (!chart) {
                renderFullTree();
            }
        });

        // Export Bagan
        $('#btn-export-img').click(function() {
            if(chart) {
                chart.exportImg({full: true});
            }
        });
    });

    function getSkeletonCards(count = 6) {
        let html = '';
        for(let i = 0; i < count; i++) {
            html += `
                <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                    <div class="tsu-skeleton-card">
                        <div>
                            <div class="tsu-skeleton mb-2" style="height: 18px; width: 70%;"></div>
                            <div class="tsu-skeleton mb-3" style="height: 14px; width: 40%;"></div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="tsu-skeleton mr-2" style="width: 38px; height: 38px; border-radius: 50%;"></div>
                                <div style="flex:1;">
                                    <div class="tsu-skeleton mb-1" style="height: 14px; width: 80%;"></div>
                                    <div class="tsu-skeleton" style="height: 12px; width: 50%;"></div>
                                </div>
                            </div>
                            <div class="tsu-skeleton" style="height: 28px; width: 100%; border-radius: 6px;"></div>
                        </div>
                        <div class="d-flex gap-2 mt-3 pt-2" style="border-top: 1px solid #f1f5f9;">
                            <div class="tsu-skeleton" style="height: 30px; width: 100px; border-radius: 6px;"></div>
                        </div>
                    </div>
                </div>
            `;
        }
        return html;
    }

    function fetchAllUnits() {
        $.ajax({
            url: "{{ route('admin.struktur-organisasi.all-units') }}",
            type: "GET",
            success: function(res) {
                if(res.success) {
                    allUnitsList = res.data;
                }
            }
        });
    }

    // Menyiapkan token CSRF untuk semua request AJAX POST
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function loadCoreUnits() {
        // Reset state
        navigationHistory = [];
        updateBreadcrumb();
        $('#btn-reset').hide();
        $('#employee-section').hide();
        $('#current-unit-title').text('Tiga Serangkai Universitas (Unit Utama)');
        $('#unit-container').html(getSkeletonCards(6));

        $.ajax({
            url: "{{ route('admin.struktur-organisasi.core-units') }}",
            type: "GET",
            success: function(response) {
                if(response.success) {
                    renderUnits(response.data);
                }
            },
            error: function() {
                $('#unit-container').html('<div class="col-12 text-center text-danger py-4"><i class="fas fa-exclamation-circle mr-1"></i> Gagal memuat data unit utama.</div>');
            }
        });
    }

    function loadUnitDetails(unitId, unitName) {
        // Tambahkan ke history jika belum ada di posisi terakhir
        if (navigationHistory.length === 0 || navigationHistory[navigationHistory.length - 1].id !== unitId) {
            navigationHistory.push({id: unitId, name: unitName});
        }
        
        updateBreadcrumb();
        $('#btn-reset').show();
        $('#current-unit-title').text('Sub-Unit dari: ' + unitName);
        $('#emp-unit-name').text(unitName);
        
        $('#unit-container').html(getSkeletonCards(3));
        $('#employee-tbody').html('<tr><td colspan="5" class="text-center py-3"><div class="spinner-border spinner-border-sm mr-1" style="color:var(--tsu-primary);"></div> Memuat data karyawan...</td></tr>');
        $('#employee-section').show();

        $.ajax({
            url: "{{ route('admin.struktur-organisasi.unit-details') }}",
            type: "POST",
            data: { id: unitId },
            success: function(response) {
                if(response.success) {
                    // Render Sub Units
                    if (response.sub_units.length > 0) {
                        renderUnits(response.sub_units);
                    } else {
                        $('#unit-container').html('<div class="col-12 text-center py-5 text-muted"><i class="fas fa-sitemap fa-2x mb-2 d-block" style="color:var(--tsu-primary-light);"></i><i>Tidak ada sub-unit di bawah unit ini.</i></div>');
                    }

                    // Render Employees
                    renderEmployees(response.employees);
                }
            },
            error: function() {
                $('#unit-container').html('<div class="col-12 text-center text-danger py-4"><i class="fas fa-exclamation-circle mr-1"></i> Gagal memuat sub-unit.</div>');
                $('#employee-tbody').html('<tr><td colspan="5" class="text-center text-danger py-3">Gagal memuat data karyawan.</td></tr>');
            }
        });
    }

    function renderUnits(units) {
        let html = '';
        units.forEach(function(unit) {
            let childIndicator = unit.has_children 
                ? `<span class="tsu-org-card__badge-sub" title="Memiliki Sub-Unit"><i class="fas fa-sitemap"></i> Sub-Unit</span>` 
                : '';
            
            let isNoHead = !unit.head_name || unit.head_name === '-' || unit.head_name === 'Kosong';
            let avatarClass = isNoHead ? 'tsu-org-leader-avatar empty' : 'tsu-org-leader-avatar';
            let avatarIcon = isNoHead ? '<i class="fas fa-user-slash"></i>' : '<i class="fas fa-user-tie"></i>';
            let leaderName = isNoHead ? 'Belum Ada Kepala' : unit.head_name;
            let leaderTitle = unit.title ? unit.title : 'Pejabat Struktural';
            let titleClass = isNoHead ? 'tsu-org-leader-title empty' : 'tsu-org-leader-title';

            html += `
                <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                    <div class="tsu-org-card h-100" onclick="loadUnitDetails('${unit.id}', '${unit.name.replace(/'/g, "\\'")}')">
                        <div class="tsu-org-card__header">
                            <h5 class="tsu-org-card__title" title="${unit.name}">${unit.name}</h5>
                            ${childIndicator}
                        </div>
                        <div class="tsu-org-card__body">
                            <div class="tsu-org-leader-box">
                                <div class="${avatarClass}">
                                    ${avatarIcon}
                                </div>
                                <div class="tsu-org-leader-info">
                                    <div class="tsu-org-leader-label">Kepala / Pimpinan</div>
                                    <div class="tsu-org-leader-name" title="${leaderName}">${leaderName}</div>
                                    <div class="${titleClass}" title="${leaderTitle}">${leaderTitle}</div>
                                </div>
                            </div>
                            <div class="tsu-org-stat-bar">
                                <span><i class="fas fa-users stat-icon"></i>Pegawai Terdaftar</span>
                                <span class="stat-count">${unit.employee_count} <span class="text-muted font-weight-normal">/ ${unit.kuota_mpp > 0 ? unit.kuota_mpp : '∞'} Kuota</span></span>
                            </div>
                        </div>
                        <div class="tsu-org-card__footer">
                            <button type="button" class="btn-move" onclick="event.stopPropagation(); showMoveUnitModal('${unit.id}', '${unit.name.replace(/'/g, "\\'")}')">
                                <i class="fas fa-exchange-alt mr-1"></i> Pindah Induk
                            </button>
                            <span class="btn-drill"><i class="fas fa-arrow-right mr-1"></i> Buka Unit</span>
                        </div>
                    </div>
                </div>
            `;
        });
        $('#unit-container').html(html);
    }

    function renderEmployees(employees) {
        let html = '';
        if (employees.length === 0) {
            html = '<tr><td colspan="5" class="text-center py-4 text-muted"><i class="fas fa-user-slash fa-2x mb-2 d-block" style="color:var(--tsu-primary-light);"></i>Tidak ada karyawan yang terdaftar langsung di unit ini.</td></tr>';
        } else {
            employees.forEach(function(emp, index) {
                let badgeClass = emp.jabatan_struktural !== 'Staf/Anggota' 
                    ? 'badge text-white font-weight-bold' 
                    : 'badge badge-light border text-muted';
                let badgeStyle = emp.jabatan_struktural !== 'Staf/Anggota'
                    ? 'background: linear-gradient(135deg, var(--tsu-primary-dark), var(--tsu-primary));'
                    : '';
                
                let tipeBadge = emp.tipe === 'Dosen'
                    ? '<span class="badge" style="background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;"><i class="fas fa-graduation-cap mr-1"></i> Dosen</span>'
                    : (emp.tipe === 'Tendik'
                        ? '<span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;"><i class="fas fa-user-tie mr-1"></i> Tendik</span>'
                        : `<span class="badge badge-light border">${emp.tipe}</span>`);

                html += `
                    <tr>
                        <td class="text-center font-weight-bold text-muted">${index + 1}</td>
                        <td class="font-weight-bold" style="color:var(--tsu-primary-dark);">${emp.nama}</td>
                        <td><span class="${badgeClass}" style="${badgeStyle}">${emp.jabatan_struktural}</span></td>
                        <td style="font-size:0.85rem;">${emp.posisi_harian || '-'}</td>
                        <td class="text-center">${tipeBadge}</td>
                    </tr>
                `;
            });
        }
        $('#employee-tbody').html(html);
    }

    function navigateToHistory(index) {
        if (index === -1) {
            loadCoreUnits();
            return;
        }
        
        // Potong history sampai index yang di-klik
        let target = navigationHistory[index];
        navigationHistory = navigationHistory.slice(0, index);
        
        // Load ulang target
        loadUnitDetails(target.id, target.name);
    }

    function updateBreadcrumb() {
        if (navigationHistory.length === 0) {
            $('#org-breadcrumb').hide();
            return;
        }

        let breadcrumbHtml = `<button type="button" class="tsu-org-trail-btn" onclick="navigateToHistory(-1)"><i class="fas fa-home"></i> Unit Utama</button>`;
        
        navigationHistory.forEach(function(item, index) {
            breadcrumbHtml += `<span class="tsu-org-trail-sep"><i class="fas fa-chevron-right"></i></span>`;
            if (index === navigationHistory.length - 1) {
                // Item aktif terakhir
                breadcrumbHtml += `<span class="tsu-org-trail-active"><i class="fas fa-folder-open mr-1" style="color:var(--tsu-primary);"></i> ${item.name}</span>`;
            } else {
                // Item history yang bisa diklik
                breadcrumbHtml += `<button type="button" class="tsu-org-trail-btn" onclick="navigateToHistory(${index})">${item.name}</button>`;
            }
        });

        $('#org-breadcrumb').html(breadcrumbHtml).show();
    }

    function showMoveUnitModal(unitId, unitName) {
        let optionsHtml = '<option value="">-- Tidak Ada (Tingkat Tertinggi) --</option>';
        allUnitsList.forEach(function(u) {
            if (u.id != unitId) {
                optionsHtml += `<option value="${u.id}">${u.nama_unit}</option>`;
            }
        });

        Swal.fire({
            title: 'Pindah Induk Unit',
            html: `
                <div class="text-left">
                    <p class="mb-2 text-muted" style="font-size:0.9rem;">Pilih unit induk baru untuk <strong style="color:var(--tsu-primary-dark);">${unitName}</strong>:</p>
                    <select id="swal-parent-unit" class="form-control text-left">
                        ${optionsHtml}
                    </select>
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: 'var(--tsu-primary)',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-save mr-1"></i> Simpan Perubahan',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                return document.getElementById('swal-parent-unit').value;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                let parentId = result.value;
                
                // Tampilkan loading
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: "{{ route('admin.struktur-organisasi.move-unit') }}",
                    type: "POST",
                    data: {
                        unit_id: unitId,
                        parent_unit_id: parentId
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonColor: 'var(--tsu-primary)'
                            });
                            // Refresh view
                            if (navigationHistory.length > 0) {
                                let currentTarget = navigationHistory[navigationHistory.length - 1];
                                navigationHistory.pop(); // Pop agar bisa push ulang di fungsi load
                                loadUnitDetails(currentTarget.id, currentTarget.name);
                            } else {
                                loadCoreUnits();
                            }
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: response.message,
                                icon: 'error',
                                confirmButtonColor: 'var(--tsu-primary)'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Terjadi kesalahan sistem.',
                            icon: 'error',
                            confirmButtonColor: 'var(--tsu-primary)'
                        });
                    }
                });
            }
        });
    }

    function renderFullTree() {
        $('#chart-container').html('<div class="d-flex justify-content-center align-items-center h-100"><div class="spinner-border" style="color:var(--tsu-primary);"></div><span class="ml-2 font-weight-bold" style="color:var(--tsu-primary-dark);">Memuat Bagan...</span></div>');
        
        d3.json("{{ route('admin.struktur-organisasi.full-tree-data') }}").then(function(response) {
            if(response.success) {
                $('#chart-container').empty();
                chart = new d3.OrgChart()
                    .container('#chart-container')
                    .data(response.data)
                    .nodeHeight((d) => d.data.type === 'employee' ? 80 : 125)
                    .nodeWidth((d) => d.data.type === 'employee' ? 240 : 280)
                    .childrenMargin((d) => 50)
                    .compactMarginBetween((d) => 15)
                    .compactMarginPair((d) => 80)
                    .onNodeClick(d => {
                        let unitId = typeof d === 'string' ? d : (d.data ? d.data.id : d);
                        if (unitId === 'root-tsu') return;
                        
                        let unitNode = response.data.find(x => x.id == unitId);

                        if (unitNode) {
                            if (unitNode.type === 'employee') {
                                // Tampilkan modal biodata ringkas karyawan
                                Swal.fire({
                                    title: '<i class="fas fa-id-card" style="color:var(--tsu-primary);"></i> Profil Karyawan',
                                    html: `
                                        <div class="text-center mt-3">
                                            <div style="width: 100px; height: 100px; border-radius: 50%; background-color: var(--tsu-primary-faint); margin: 0 auto 15px auto; display: flex; align-items: center; justify-content: center; border: 3px solid var(--tsu-primary-light); color: var(--tsu-primary); font-size: 45px; overflow: hidden;">
                                                ${unitNode.image_url ? `<img src="${unitNode.image_url}" style="width:100%;height:100%;object-fit:cover;">` : `<i class="fas fa-user"></i>`}
                                            </div>
                                            <h5 class="font-weight-bold mb-1" style="color:var(--tsu-primary-dark);">${unitNode.name}</h5>
                                            <span class="badge badge-light border mb-3">${unitNode.tipe_karyawan}</span>
                                            
                                            <div class="text-left bg-light p-3 rounded border">
                                                <p class="mb-2"><b><i class="fas fa-sitemap text-muted"></i> Jabatan / Status:</b><br><span class="badge text-white mt-1" style="background:linear-gradient(135deg,var(--tsu-primary-dark),var(--tsu-primary));">${unitNode.title}</span></p>
                                                <p class="mb-0"><b><i class="fas fa-briefcase text-muted"></i> Posisi Harian:</b><br>${unitNode.posisi}</p>
                                            </div>
                                        </div>
                                    `,
                                    showCloseButton: true,
                                    showConfirmButton: false,
                                    width: '450px'
                                });
                                return; // Hentikan eksekusi di sini agar tidak memuat unit modal
                            }

                            // Tampilkan loading modal untuk Unit Node
                            Swal.fire({
                                title: 'Memuat data...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            // Ambil data detail unit menggunakan endpoint yang sama dengan mode kartu
                            $.ajax({
                                url: "{{ route('admin.struktur-organisasi.unit-details') }}",
                                type: "POST",
                                data: { id: unitNode.id },
                                success: function(res) {
                                    if(res.success) {
                                        let empHtml = '';
                                        if(res.employees.length === 0) {
                                            empHtml = '<p class="text-muted text-center mt-3"><i>Tidak ada karyawan di unit ini.</i></p>';
                                        } else {
                                            empHtml = '<div class="table-responsive mt-3" style="max-height: 300px; overflow-y: auto;"><table class="table table-sm table-hover text-left" style="font-size: 14px;"><thead style="background:var(--tsu-primary-faint);"><tr><th>Nama Karyawan</th><th>Jabatan / Posisi</th></tr></thead><tbody>';
                                            res.employees.forEach(function(emp) {
                                                let badgeClass = emp.jabatan_struktural !== 'Staf/Anggota' ? 'badge text-white' : 'badge badge-light border';
                                                let badgeStyle = emp.jabatan_struktural !== 'Staf/Anggota' ? 'background:linear-gradient(135deg,var(--tsu-primary-dark),var(--tsu-primary));' : '';
                                                empHtml += `<tr>
                                                    <td><span class="font-weight-bold" style="color:var(--tsu-primary-dark);">${emp.nama}</span><br><small class="text-muted">${emp.tipe}</small></td>
                                                    <td><span class="${badgeClass} mb-1" style="${badgeStyle}">${emp.jabatan_struktural}</span><br><small class="text-muted">${emp.posisi_harian || '-'}</small></td>
                                                </tr>`;
                                            });
                                            empHtml += '</tbody></table></div>';
                                        }

                                        Swal.fire({
                                            title: '<i class="fas fa-building mr-1" style="color:var(--tsu-primary);"></i> ' + unitNode.name,
                                            html: `
                                                <div class="text-left">
                                                    <div class="p-3 rounded border" style="background:var(--tsu-primary-faint);border-color:var(--tsu-primary-light)!important;">
                                                        <p class="mb-1"><b><i class="fas fa-user-tie mr-1" style="color:var(--tsu-primary);"></i> Kepala / Pimpinan:</b><br>${unitNode.head_name} <span class="badge text-white ml-1" style="background:var(--tsu-primary);">${unitNode.title}</span></p>
                                                        <p class="mb-0 mt-2"><b><i class="fas fa-users mr-1" style="color:var(--tsu-primary);"></i> Total Karyawan:</b> ${res.employees.length} Orang</p>
                                                    </div>
                                                    <h6 class="font-weight-bold mt-4 mb-2" style="color:var(--tsu-primary-dark);">Daftar Karyawan:</h6>
                                                    ${empHtml}
                                                </div>
                                            `,
                                            width: '700px',
                                            showCloseButton: true,
                                            showConfirmButton: false
                                        });
                                    } else {
                                        Swal.fire({
                                            title: 'Error',
                                            text: 'Gagal memuat detail unit.',
                                            icon: 'error',
                                            confirmButtonColor: 'var(--tsu-primary)'
                                        });
                                    }
                                },
                                error: function() {
                                    Swal.fire({
                                        title: 'Error',
                                        text: 'Terjadi kesalahan jaringan.',
                                        icon: 'error',
                                        confirmButtonColor: 'var(--tsu-primary)'
                                    });
                                }
                            });
                        }
                    })
                    .nodeContent(function(d, i, arr, state) {
                        if (d.data.type === 'employee') {
                            return `
                                <div style="background-color: #ffffff; border: 1.5px solid var(--tsu-primary-light, #d0eef2); border-radius: 8px; width: ${d.width}px; height: ${d.height}px; padding: 10px; display: flex; align-items: center; box-shadow: 0 2px 6px rgba(9,75,84,0.08);">
                                    <div style="min-width: 40px; height: 40px; border-radius: 50%; background-color: #f1fbfc; display: flex; align-items: center; justify-content: center; margin-right: 12px; border: 1.5px solid #d0eef2; color: #1d7a87; font-size: 16px; overflow: hidden;">
                                        ${d.data.image_url ? `<img src="${d.data.image_url}" style="width:100%;height:100%;object-fit:cover;">` : `<i class="fas fa-user"></i>`}
                                    </div>
                                    <div style="flex: 1; overflow: hidden;">
                                        <h6 style="margin: 0 0 3px 0; font-size: 12px; color: #094b54; font-weight: bold; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${d.data.name}">${d.data.name}</h6>
                                        <div style="font-size: 10px; color: #6c757d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${d.data.title}">${d.data.title}</div>
                                        <div style="font-size: 10px; color: #1d7a87; margin-top: 2px; font-weight: 600;">${d.data.posisi}</div>
                                    </div>
                                </div>
                            `;
                        }

                        // Tampilan untuk Unit Node
                        return `
                            <div style="background-color: #ffffff; border-top: 4px solid #1d7a87; border: 1.5px solid #d0eef2; border-top: 4px solid #1d7a87; border-radius: 10px; width: ${d.width}px; height: ${d.height}px; padding: 15px; display: flex; align-items: center; box-shadow: 0 4px 12px rgba(9,75,84,0.1);">
                                <div style="min-width: 52px; height: 52px; border-radius: 50%; background: linear-gradient(135deg, #094b54, #1d7a87); display: flex; align-items: center; justify-content: center; margin-right: 14px; color: #ffffff; font-size: 22px; overflow: hidden; box-shadow: 0 2px 6px rgba(9,75,84,0.2);">
                                    ${d.data.image_url && d.data.head_name !== 'Kosong' ? `<img src="${d.data.image_url}" style="width:100%;height:100%;object-fit:cover;">` : `<i class="fas fa-user-tie"></i>`}
                                </div>
                                <div style="flex: 1; overflow: hidden;">
                                    <h6 style="margin: 0 0 4px 0; font-size: 13.5px; color: #094b54; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${d.data.name}">${d.data.name}</h6>
                                    <div style="font-size: 12px; color: #1e293b; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${d.data.head_name}">${d.data.head_name}</div>
                                    <div style="font-size: 11px; color: #1d7a87; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${d.data.title}">${d.data.title}</div>
                                    <div style="font-size: 11px; color: #16a34a; margin-top: 4px; font-weight: 700;"><i class="fas fa-users mr-1"></i>${d.data.employee_count} / ${d.data.kuota_mpp > 0 ? d.data.kuota_mpp : '∞'} Pegawai</div>
                                </div>
                            </div>
                        `;
                    })
                    .render()
                    .fit();
            }
        }).catch(function(error) {
            console.error("D3 Org Chart Error:", error);
            $('#chart-container').html('<div class="d-flex justify-content-center align-items-center h-100 text-danger"><i class="fas fa-exclamation-triangle mr-2"></i> Gagal memuat data bagan. Cek console log.</div>');
        });
    }
</script>
@endsection
